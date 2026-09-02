<?php

declare(strict_types=1);

namespace App\Modules\Admission\Services;

use App\Models\Admission\ApprovalStep;
use App\Models\Admission\Decision;
use App\Models\Admission\DocumentVerification;
use App\Models\Admission\PaymentReceipt;
use App\Models\Admission\PaymentTransaction;
use App\Models\Admission\ReviewAssignment;
use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\AuditLog;
use App\Models\User;
use App\Modules\Admission\Workspaces\ApprovalWorkspace;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The operational seam behind the staff workspaces. Every workspace reads rows
 * that some part of the applicant or staff lifecycle must first write; this
 * service owns those writes so no screen has to invent its own.
 */
final class AdmissionPipeline
{
    public const STAGE_TRIAGE = 'triage';

    public const STAGE_DOCUMENT_VERIFICATION = 'document_verification';

    public const STAGE_DEPARTMENT_REVIEW = 'department_review';

    /** Default turnaround when the intake does not set one. */
    private const DEFAULT_SLA_DAYS = 5;

    /**
     * Put a submitted application on a desk. Idempotent per stage so a repeated
     * submission or a retried job cannot double-queue the same work.
     */
    public function openAssignment(
        AdmissionApplication $application,
        string $stage = self::STAGE_TRIAGE,
        ?int $assigneeId = null,
        ?int $assignedBy = null,
    ): ?ReviewAssignment {
        $existing = ReviewAssignment::query()
            ->where('admission_application_id', $application->id)
            ->where('stage', $stage)
            ->whereIn('status', ['PENDING', 'IN_PROGRESS', 'DELEGATED'])
            ->first();
        if ($existing !== null) {
            return $existing;
        }

        $assigneeId ??= $this->nextReviewer();
        if ($assigneeId === null) {
            // No reviewer pool means no assignment: the work stays visible as
            // unassigned triage backlog rather than being silently dropped.
            return null;
        }

        $dueAt = now()->addDays($this->slaDays($application));
        $assignment = ReviewAssignment::create([
            'admission_application_id' => $application->id,
            'assignee_id' => $assigneeId,
            'assigned_by' => $assignedBy,
            'stage' => $stage,
            'role_code' => $stage === self::STAGE_DEPARTMENT_REVIEW ? 'DEPARTMENT_REVIEWER' : 'ADMISSIONS_OFFICER',
            'status' => 'PENDING',
            'priority' => $this->priorityFor($application),
            'due_at' => $dueAt,
        ]);

        $application->forceFill(['sla_due_at' => $application->sla_due_at ?? $dueAt, 'last_activity_at' => now()])->save();
        AuditLog::record('admission.assignment_opened', $application, null, [
            'stage' => $stage, 'assignee_id' => $assigneeId, 'due_at' => $dueAt->toIso8601String(),
        ]);

        return $assignment;
    }

    /**
     * Sweep every unassigned live application onto a desk. Returns how many were
     * queued so the caller can report a real number.
     */
    public function autoAssign(?int $assignedBy = null, int $limit = 250): int
    {
        $unassigned = AdmissionApplication::query()
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])
            ->whereDoesntHave('reviewAssignments', fn ($q) => $q->whereIn('status', ['PENDING', 'IN_PROGRESS', 'DELEGATED']))
            ->limit($limit)
            ->get();

        $assigned = 0;
        foreach ($unassigned as $application) {
            if ($this->openAssignment($application, self::STAGE_TRIAGE, null, $assignedBy) !== null) {
                $assigned++;
            }
        }

        return $assigned;
    }

    /** Close the open assignment for a stage once its work has been recorded. */
    public function completeAssignment(AdmissionApplication $application, string $stage): void
    {
        ReviewAssignment::query()
            ->where('admission_application_id', $application->id)
            ->where('stage', $stage)
            ->whereIn('status', ['PENDING', 'IN_PROGRESS', 'DELEGATED'])
            ->update(['status' => 'COMPLETED', 'completed_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Record the adjudication behind a status change. Decisions are the audit
     * record of *why* an application moved, separate from the status itself.
     */
    public function recordDecision(
        AdmissionApplication $application,
        string $decisionType,
        string $outcome,
        int $decidedBy,
        ?string $reasonCode = null,
        ?string $rationale = null,
        bool $isFinal = false,
    ): Decision {
        return Decision::create([
            'admission_application_id' => $application->id,
            'decision_type' => $decisionType,
            'outcome' => $outcome,
            'reason_code' => $reasonCode,
            'rationale' => $rationale,
            'is_final' => $isFinal,
            'decided_by' => $decidedBy,
            'decided_by_role' => User::find($decidedBy)?->role,
            'decided_at' => now(),
        ]);
    }

    /**
     * Open the dean-then-board signature ladder. Steps are created pending with
     * no approver so the workspace shows who still owes a signature.
     */
    public function openApprovalLadder(AdmissionApplication $application): void
    {
        foreach (ApprovalWorkspace::LADDER as $index => $roleCode) {
            ApprovalStep::firstOrCreate(
                ['admission_application_id' => $application->id, 'step_order' => $index + 1],
                ['role_code' => $roleCode, 'status' => 'PENDING'],
            );
        }
    }

    /** Sign, or refuse to sign, one rung of the approval ladder. */
    public function actOnApproval(ApprovalStep $step, string $status, int $approverId, ?string $comment = null): ApprovalStep
    {
        $step->update([
            'status' => $status,
            'approver_id' => $approverId,
            'acted_at' => now(),
            'comment' => $comment,
        ]);
        AuditLog::record('admission.approval_recorded', $step->application ?? $step, null, [
            'role' => $step->role_code, 'status' => $status, 'approver_id' => $approverId,
        ]);

        return $step->refresh();
    }

    /**
     * Promote a confirmed payment attempt into the ledger. The attempt records
     * intent; the transaction and receipt are the accounting artefacts staff
     * reconcile against, so both are written in one transaction.
     */
    public function recordPayment(AdmissionApplication $application, object $attempt, ?int $recordedBy = null): ?PaymentTransaction
    {
        if (! in_array($attempt->status, ['PAID', 'WAIVED'], true)) {
            return null;
        }

        return DB::transaction(function () use ($application, $attempt, $recordedBy): PaymentTransaction {
            $transaction = PaymentTransaction::firstOrCreate(
                ['application_payment_attempt_id' => $attempt->id],
                [
                    'admission_application_id' => $application->id,
                    'provider' => $attempt->provider ?? $attempt->channel ?? 'MANUAL',
                    'provider_transaction_ref' => $attempt->provider_request_ref ?? $attempt->reference,
                    'amount' => $attempt->amount,
                    'currency' => $attempt->currency ?? 'KES',
                    'expected_amount' => $attempt->expected_amount ?? $attempt->amount,
                    'transaction_time' => $attempt->paid_at ?? now(),
                    'status' => $attempt->status,
                    'is_authoritative_fee' => true,
                    'reconciliation_state' => 'UNMATCHED',
                    'recorded_by' => $recordedBy,
                ],
            );

            PaymentReceipt::firstOrCreate(
                ['payment_transaction_id' => $transaction->id],
                [
                    'admission_application_id' => $application->id,
                    'receipt_number' => $attempt->receipt_number ?? 'RCT-'.strtoupper(substr(str_replace('-', '', (string) $transaction->id), 0, 12)),
                    'amount' => $attempt->amount,
                    'currency' => $attempt->currency ?? 'KES',
                    'payment_method' => $attempt->channel ?? 'MANUAL',
                    'issued_by' => $recordedBy,
                    'checksum' => hash('sha256', $transaction->id.$attempt->amount.($attempt->reference ?? '')),
                ],
            );

            return $transaction;
        });
    }

    /**
     * Record a verifier's decision on one document. The document row carries the
     * current state; document_verifications carries the history of who said so.
     */
    public function verifyDocument(ApplicationDocument $document, string $outcome, int $verifierId, ?string $note = null): DocumentVerification
    {
        return DB::transaction(function () use ($document, $outcome, $verifierId, $note): DocumentVerification {
            $document->update(['verification_status' => $outcome, 'verified_by' => $verifierId]);

            return DocumentVerification::create([
                'application_document_id' => $document->id,
                'verifier_id' => $verifierId,
                'outcome' => $outcome,
                'notes' => $note,
                'evidence_hash' => $document->sha256,
                'verified_at' => now(),
            ]);
        });
    }

    /** Round-robin over the reviewer pool, favouring whoever holds least work. */
    private function nextReviewer(): ?int
    {
        $pool = $this->reviewerPool();
        if ($pool->isEmpty()) {
            return null;
        }

        $load = DB::table('review_assignments')
            ->whereIn('status', ['PENDING', 'IN_PROGRESS', 'DELEGATED'])
            ->whereIn('assignee_id', $pool)
            ->selectRaw('assignee_id, count(*) as open_count')
            ->groupBy('assignee_id')
            ->pluck('open_count', 'assignee_id');

        return $pool->sortBy(fn (int $id): int => (int) ($load[$id] ?? 0))->first();
    }

    /** @return Collection<int, int> */
    private function reviewerPool(): Collection
    {
        return User::query()->whereIn('role', ['admin', 'staff'])->orderBy('id')->pluck('id');
    }

    private function slaDays(AdmissionApplication $application): int
    {
        return (int) ($application->offering?->intake?->sla_review_days ?: self::DEFAULT_SLA_DAYS);
    }

    /**
     * Priority is the SLA clock, not a clerk's opinion: the closer an intake is
     * to closing, the higher the application sorts.
     */
    private function priorityFor(AdmissionApplication $application): int
    {
        $closesAt = $application->offering?->intake?->closes_at;
        if ($closesAt === null) {
            return 6;
        }

        return match (true) {
            now()->diffInDays($closesAt, false) <= 3 => 1,
            now()->diffInDays($closesAt, false) <= 14 => 4,
            default => 6,
        };
    }
}
