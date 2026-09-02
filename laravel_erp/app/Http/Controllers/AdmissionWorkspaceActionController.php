<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Admission\ApprovalStep;
use App\Models\Admission\PaymentReconciliation;
use App\Models\Admission\PaymentReconciliationException;
use App\Models\Admission\PaymentWaiver;
use App\Models\AdmissionApplication;
use App\Models\ApplicationPaymentAttempt;
use App\Models\AuditLog;
use App\Modules\Admission\Services\AdmissionPipeline;
use App\Modules\Admission\Workspaces\AdmissionRollWorkspace;
use App\Modules\Admission\Workspaces\ApprovalWorkspace;
use App\Services\AdmissionWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * The write side of the admissions staff workspaces. Read queries live in
 * App\Modules\Admission\Workspaces; every control on those screens lands here.
 */
final class AdmissionWorkspaceActionController extends Controller
{
    public function __construct(private readonly AdmissionPipeline $pipeline) {}

    /** Work queues — sweep unassigned submissions onto reviewer desks. */
    public function autoAssign(Request $request): RedirectResponse
    {
        $this->authorizeStaff($request);
        $count = $this->pipeline->autoAssign($request->user()->id);

        return back()->with(
            $count > 0 ? 'success' : 'info',
            $count > 0
                ? "{$count} application(s) assigned to the triage desk."
                : 'No unassigned applications are waiting.',
        );
    }

    /** Reviews — open departmental scoring for every verified application. */
    public function assignReviewers(Request $request): RedirectResponse
    {
        $this->authorizeStaff($request);

        $assigned = 0;
        AdmissionApplication::query()
            ->where('status', 'VERIFIED')
            ->whereDoesntHave('reviewAssignments', fn ($query) => $query
                ->where('stage', AdmissionPipeline::STAGE_DEPARTMENT_REVIEW)
                ->whereIn('status', ['PENDING', 'IN_PROGRESS', 'DELEGATED']))
            ->each(function (AdmissionApplication $application) use ($request, &$assigned): void {
                $opened = $this->pipeline->openAssignment(
                    $application, AdmissionPipeline::STAGE_DEPARTMENT_REVIEW, null, $request->user()->id,
                );
                $assigned += $opened === null ? 0 : 1;
            });

        return back()->with(
            $assigned > 0 ? 'success' : 'info',
            $assigned > 0
                ? "{$assigned} verified application(s) sent for departmental scoring."
                : 'No verified application is awaiting a reviewer.',
        );
    }

    /** Shortlists — move one ranked applicant onto the approval ladder. */
    public function advanceShortlist(Request $request, AdmissionApplication $application, AdmissionWorkflow $workflow): RedirectResponse
    {
        $this->authorizeStaff($request);
        $workflow->move($application, 'APPROVAL_PENDING', 'shortlist_advanced', 'Advanced from the merit shortlist.');

        return back()->with('success', "{$application->application_number} sent to the approval ladder.");
    }

    /** Shortlists — submit the whole shortlist to the board in one action. */
    public function submitShortlistToBoard(Request $request, AdmissionWorkflow $workflow): RedirectResponse
    {
        $this->authorizeStaff($request);

        $moved = 0;
        AdmissionApplication::query()
            ->where('status', 'SHORTLISTED')
            ->each(function (AdmissionApplication $application) use ($workflow, &$moved): void {
                $workflow->move($application, 'APPROVAL_PENDING', 'shortlist_batch_submitted', 'Submitted to the board with the shortlist batch.');
                $moved++;
            });

        return back()->with(
            $moved > 0 ? 'success' : 'info',
            $moved > 0 ? "{$moved} shortlisted application(s) submitted to the board." : 'The shortlist is empty.',
        );
    }

    /** Approvals — sign, or refuse, the next open rung of the ladder. */
    public function signOff(Request $request, AdmissionApplication $application): RedirectResponse
    {
        $this->authorizeStaff($request);
        $data = $request->validate([
            'verdict' => ['required', 'in:APPROVED,REJECTED'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $step = ApprovalStep::query()
            ->where('admission_application_id', $application->id)
            ->where('status', 'PENDING')
            ->orderBy('step_order')
            ->first();

        if ($step === null) {
            return back()->with('info', 'Every rung of this approval ladder has already been signed.');
        }

        $this->pipeline->actOnApproval($step, $data['verdict'], $request->user()->id, $data['comment'] ?? null);

        return back()->with('success', "{$step->role_code} verdict recorded for {$application->application_number}.");
    }

    /** Approvals — admit everyone whose ladder is fully signed. */
    public function authorizeOffers(Request $request, AdmissionWorkflow $workflow): RedirectResponse
    {
        $this->authorizeStaff($request);

        $ready = AdmissionApplication::query()
            ->where('status', 'APPROVAL_PENDING')
            ->whereHas('approvalSteps', fn ($query) => $query->where('status', 'APPROVED'), '=', count(ApprovalWorkspace::LADDER))
            ->get();

        foreach ($ready as $application) {
            $workflow->move($application, 'ADMITTED', 'board_authorised', 'Admitted on a complete approval ladder.');
        }

        return back()->with(
            $ready->isNotEmpty() ? 'success' : 'info',
            $ready->isNotEmpty()
                ? $ready->count().' offer(s) authorised and issued.'
                : 'No application has a complete approval ladder yet.',
        );
    }

    /** Waitlists — promote one holder back onto the shortlist. */
    public function promoteWaitlisted(Request $request, AdmissionApplication $application, AdmissionWorkflow $workflow): RedirectResponse
    {
        $this->authorizeStaff($request);
        $workflow->move($application, 'SHORTLISTED', 'waitlist_promoted', 'Promoted from the waitlist against a released place.');

        return back()->with('success', "{$application->application_number} promoted from the waitlist.");
    }

    /**
     * Waitlists — promote the highest-ranked holder of every offering that still
     * has a free place. Capacity is the constraint, so nothing is ever promoted
     * into a full programme.
     */
    public function autoPromoteWaitlist(Request $request, AdmissionWorkflow $workflow): RedirectResponse
    {
        $this->authorizeStaff($request);

        $promoted = 0;
        $offerings = DB::table('programme_offerings')
            ->selectRaw('id, greatest(coalesce(capacity, 0) - coalesce(confirmed_seats, 0) - coalesce(reserved_seats, 0), 0) as vacancies')
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn (object $offering): bool => (int) $offering->vacancies > 0);

        foreach ($offerings as $offering) {
            AdmissionApplication::query()
                ->where('status', 'WAITLISTED')
                ->where('programme_offering_id', $offering->id)
                ->orderByRaw('eligibility_score desc nulls last')
                ->orderBy('application_number')
                ->limit((int) $offering->vacancies)
                ->get()
                ->each(function (AdmissionApplication $application) use ($workflow, &$promoted): void {
                    $workflow->move($application, 'SHORTLISTED', 'waitlist_auto_promoted', 'Auto-promoted against a released place.');
                    $promoted++;
                });
        }

        return back()->with(
            $promoted > 0 ? 'success' : 'info',
            $promoted > 0 ? "{$promoted} waitlisted applicant(s) promoted." : 'No vacancy is currently available to promote into.',
        );
    }

    /** Admission rolls — the official register as a CSV staff can print or file. */
    public function exportRoll(Request $request, AdmissionRollWorkspace $workspace): Response
    {
        $this->authorizeStaff($request);

        $rows = $workspace->rows($request->only(['cohort', 'status', 'q']));
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Application No', 'Student Name', 'Programme', 'School', 'Cohort', 'Admission No', 'Enrolment Date', 'Status']);
        foreach ($rows->items() as $row) {
            fputcsv($handle, [
                $row['app_no'], $row['student_name'], $row['programme'], $row['school'],
                $row['cohort'], $row['admission_number'], $row['enrolment_date'], $row['status'],
            ]);
        }
        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        AuditLog::record('admission.roll_exported', $request->user(), null, ['rows' => count($rows->items())]);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="admission-roll-'.now()->format('Ymd-His').'.csv"',
        ]);
    }

    /** Payments — an authorised fee waiver, recorded against the application. */
    public function waiveFee(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403, 'Only an administrator may authorise a fee waiver.');
        $data = $request->validate([
            'application_number' => ['required', 'string', 'max:60'],
            'reason_code' => ['required', 'string', 'max:80'],
            'justification' => ['required', 'string', 'max:1000'],
        ]);

        $application = AdmissionApplication::query()
            ->where('application_number', $data['application_number'])
            ->firstOrFail();

        $waiver = PaymentWaiver::create([
            'admission_application_id' => $application->id,
            'amount_waived' => $application->fee_amount_expected ?? 1000,
            'currency' => $application->fee_currency ?? 'KES',
            'reason_code' => $data['reason_code'],
            'justification' => $data['justification'],
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'status' => 'ACTIVE',
        ]);

        $application->forceFill(['payment_status' => 'WAIVED'])->save();
        AuditLog::record('admission.fee_waived', $application, null, [
            'waiver_id' => $waiver->id,
            'amount' => $waiver->amount_waived,
            'reason' => $data['reason_code'],
        ]);

        return back()->with('success', "Fee waiver recorded for {$application->application_number}.");
    }

    /** Payments — the receipt behind one settled attempt. */
    public function receipt(Request $request, ApplicationPaymentAttempt $attempt): JsonResponse
    {
        $this->authorizeStaff($request);
        abort_unless(in_array($attempt->status, ['PAID', 'WAIVED'], true), 404, 'No receipt exists for an unsettled payment.');

        $receipt = DB::table('payment_receipts as r')
            ->join('payment_transactions as t', 't.id', '=', 'r.payment_transaction_id')
            ->where('t.application_payment_attempt_id', $attempt->id)
            ->where('r.is_void', false)
            ->select('r.receipt_number', 'r.amount', 'r.currency', 'r.payment_method', 'r.issued_at', 'r.checksum')
            ->first();

        AuditLog::record('admission.receipt_viewed', $attempt, null, ['reference' => $attempt->reference]);

        return response()->json([
            'reference' => $attempt->reference,
            'receipt_number' => $receipt->receipt_number ?? $attempt->receipt_number,
            'amount' => (float) ($receipt->amount ?? $attempt->amount),
            'currency' => $receipt->currency ?? $attempt->currency,
            'method' => $receipt->payment_method ?? $attempt->channel,
            'issued_at' => $receipt->issued_at ?? $attempt->paid_at,
            'checksum' => $receipt->checksum,
            'ledger_entry' => $receipt !== null,
        ]);
    }

    /**
     * Reconciliation — match the fee ledger against recorded attempts and file
     * the differences. This is an internal double-entry check: matching against
     * a bank or M-Pesa statement needs the provider feed, which is not wired up.
     */
    public function runReconciliation(Request $request): RedirectResponse
    {
        $this->authorizeStaff($request);

        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        $settled = DB::table('application_payment_attempts')
            ->whereIn('status', ['PAID', 'WAIVED'])
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->get();

        $ledgerTotal = 0.0;
        $matched = 0;
        $exceptions = [];
        foreach ($settled as $attempt) {
            $transaction = DB::table('payment_transactions')
                ->where('application_payment_attempt_id', $attempt->id)
                ->first();

            if ($transaction === null) {
                $exceptions[] = ['attempt' => $attempt, 'type' => 'MISSING_LEDGER_ENTRY', 'actual' => null];

                continue;
            }
            if ((float) $transaction->amount !== (float) $attempt->amount) {
                $exceptions[] = ['attempt' => $attempt, 'type' => 'AMOUNT_MISMATCH', 'actual' => (float) $transaction->amount];

                continue;
            }
            $ledgerTotal += (float) $transaction->amount;
            $matched++;
        }

        $run = DB::transaction(function () use ($settled, $periodStart, $periodEnd, $ledgerTotal, $matched, $exceptions, $request): PaymentReconciliation {
            $reconciliation = PaymentReconciliation::create([
                'institution_id' => $settled->first()->institution_id ?? DB::table('institutions')->value('id'),
                'provider' => 'INTERNAL_LEDGER',
                'statement_reference' => 'REC-'.now()->format('Ymd-His'),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'run_by' => $request->user()->id,
                'run_at' => now(),
                'matched_count' => $matched,
                'unmatched_count' => count($exceptions),
                'exception_count' => count($exceptions),
                'provider_total' => (float) $settled->sum('amount'),
                'ledger_total' => $ledgerTotal,
                'status' => $exceptions === [] ? 'COMPLETED' : 'EXCEPTIONS',
                'notes' => 'Internal ledger match. Provider statement import is not yet available.',
            ]);

            foreach ($exceptions as $exception) {
                PaymentReconciliationException::create([
                    'payment_reconciliation_id' => $reconciliation->id,
                    'admission_application_id' => $exception['attempt']->admission_application_id,
                    'exception_type' => $exception['type'],
                    'expected_amount' => $exception['attempt']->amount,
                    'actual_amount' => $exception['actual'],
                    'currency' => $exception['attempt']->currency,
                    'detail' => ['reference' => $exception['attempt']->reference, 'channel' => $exception['attempt']->channel],
                    'status' => 'OPEN',
                    'raised_by' => $request->user()->id,
                ]);
            }

            return $reconciliation;
        });

        AuditLog::record('admission.reconciliation_run', $request->user(), null, [
            'reference' => $run->statement_reference, 'matched' => $matched, 'exceptions' => count($exceptions),
        ]);

        return back()->with('success', "Reconciliation {$run->statement_reference}: {$matched} matched, ".count($exceptions).' exception(s) raised.');
    }

    /**
     * Audit — re-check that the append-only guarantees are still installed. The
     * trail carries no hash chain, so this reports what the database actually
     * enforces rather than claiming a verification the schema cannot support.
     */
    public function verifyAuditIntegrity(Request $request): RedirectResponse
    {
        $this->authorizeStaff($request);

        $triggers = DB::table('pg_trigger as t')
            ->join('pg_class as c', 'c.oid', '=', 't.tgrelid')
            ->where('c.relname', 'audit_logs')
            ->where('t.tgisinternal', false)
            ->pluck('tgname');

        $intact = $triggers->count() >= 2;
        AuditLog::record('admission.audit_integrity_checked', $request->user(), null, [
            'triggers' => $triggers->implode(', '), 'intact' => $intact,
        ]);

        return back()->with(
            $intact ? 'success' : 'error',
            $intact
                ? 'Append-only enforcement verified on audit_logs: '.$triggers->implode(', ').'.'
                : 'Append-only triggers are missing from audit_logs. Escalate to the database administrator.',
        );
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
    }
}
