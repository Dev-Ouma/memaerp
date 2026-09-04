<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CourseEnrolment;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeePaymentAccount;
use App\Models\FeeStructure;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registration → Fees vertical: open a period, enrol a student, invoice from a
 * programme fee structure, and record/confirm tuition payments.
 */
final class RegistrationFeesService
{
    public function createPeriod(array $data): RegistrationPeriod
    {
        return RegistrationPeriod::query()->create([
            'code' => strtoupper(trim((string) $data['code'])),
            'title' => trim((string) $data['title']),
            'academic_session_id' => $data['academic_session_id'] ?? null,
            'starts_on' => $data['starts_on'],
            'regular_deadline' => $data['regular_deadline'],
            'late_deadline' => $data['late_deadline'] ?? $data['regular_deadline'],
            'min_units' => (int) ($data['min_units'] ?? 1),
            'max_units' => (int) ($data['max_units'] ?? 8),
            'financial_gating' => filter_var($data['financial_gating'] ?? true, FILTER_VALIDATE_BOOL),
            'late_penalty_amount' => (float) ($data['late_penalty_amount'] ?? 0),
            'status' => strtoupper((string) ($data['status'] ?? 'OPEN')),
        ]);
    }

    public function createStructure(array $data): FeeStructure
    {
        return FeeStructure::query()->create([
            'code' => strtoupper(trim((string) $data['code'])),
            'title' => trim((string) $data['title']),
            'course_id' => $data['course_id'] ?? null,
            'cohort' => $data['cohort'] ?? null,
            'tuition_amount' => (float) ($data['tuition_amount'] ?? 0),
            'admin_amount' => (float) ($data['admin_amount'] ?? 0),
            'currency' => strtoupper((string) ($data['currency'] ?? 'KES')),
            'status' => strtoupper((string) ($data['status'] ?? 'ACTIVE')),
        ]);
    }

    /**
     * Enrol a student in an open period and auto-invoice against the matching
     * programme fee structure when financial gating is enabled.
     */
    public function enrolStudent(RegistrationPeriod $period, Student $student, ?int $subjectId = null): CourseEnrolment
    {
        if (! $period->isOpen()) {
            throw ValidationException::withMessages(['period' => 'Course registration is only allowed in an OPEN period.']);
        }

        $exists = CourseEnrolment::query()
            ->where('registration_period_id', $period->id)
            ->where('student_id', $student->id)
            ->when(
                $subjectId === null,
                fn ($q) => $q->whereNull('subject_id'),
                fn ($q) => $q->where('subject_id', $subjectId),
            )
            ->where('status', 'REGISTERED')
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages(['student_id' => 'This student is already registered for the selected period/unit.']);
        }

        return DB::transaction(function () use ($period, $student, $subjectId): CourseEnrolment {
            $enrolment = CourseEnrolment::query()->create([
                'registration_period_id' => $period->id,
                'student_id' => $student->id,
                'subject_id' => $subjectId,
                'status' => 'REGISTERED',
                'registered_at' => now(),
            ]);

            if ($period->financial_gating) {
                $structure = FeeStructure::query()
                    ->where('status', 'ACTIVE')
                    ->where(function ($query) use ($student): void {
                        $query->whereNull('course_id')->orWhere('course_id', $student->course_id);
                    })
                    ->orderByRaw('case when course_id is null then 1 else 0 end')
                    ->first();

                if ($structure !== null) {
                    $this->issueInvoice($student, $structure, $period, $enrolment);
                }
            }

            return $enrolment->fresh(['period', 'student', 'invoice']) ?? $enrolment;
        });
    }

    public function issueInvoice(
        Student $student,
        FeeStructure $structure,
        ?RegistrationPeriod $period = null,
        ?CourseEnrolment $enrolment = null,
    ): FeeInvoice {
        abort_unless($structure->status === 'ACTIVE', 422, 'Fee structure is not active.');

        return FeeInvoice::query()->create([
            'invoice_no' => $this->nextRef('INV'),
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'registration_period_id' => $period?->id,
            'course_enrolment_id' => $enrolment?->id,
            'amount_invoiced' => $structure->totalAmount(),
            'amount_paid' => 0,
            'status' => 'OPEN',
            'issued_at' => now(),
        ]);
    }

    public function recordPayment(
        FeeInvoice $invoice,
        float $amount,
        string $method,
        ?string $transactionRef = null,
        ?User $actor = null,
        bool $confirm = true,
        ?int $paymentAccountId = null,
    ): FeePayment {
        abort_if($amount <= 0, 422, 'Payment amount must be positive.');
        abort_if($invoice->status === 'CANCELLED', 422, 'Cannot pay a cancelled invoice.');

        return DB::transaction(function () use ($invoice, $amount, $method, $transactionRef, $actor, $confirm, $paymentAccountId): FeePayment {
            $payment = FeePayment::query()->create([
                'payment_ref' => $this->nextRef('PAY'),
                'fee_invoice_id' => $invoice->id,
                'payment_account_id' => $paymentAccountId,
                'amount' => $amount,
                'method' => strtoupper($method),
                'transaction_ref' => $transactionRef,
                'status' => $confirm ? 'CONFIRMED' : 'PENDING',
                'receipt_no' => $confirm ? $this->nextRef('RCPT') : null,
                'paid_at' => $confirm ? now() : null,
                'recorded_by' => $actor?->id,
            ]);

            if ($confirm) {
                $invoice->amount_paid = (float) $invoice->amount_paid + $amount;
                $invoice->refreshSettlementStatus();
            }

            return $payment->fresh(['invoice.student.user', 'account']) ?? $payment;
        });
    }

    public function createPaymentAccount(array $data): FeePaymentAccount
    {
        return FeePaymentAccount::query()->create([
            'code' => strtoupper(trim((string) $data['code'])),
            'name' => trim((string) $data['name']),
            'bank_name' => $data['bank_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'integration_type' => $data['integration_type'] ?? null,
            'status' => strtoupper((string) ($data['status'] ?? 'ACTIVE')),
        ]);
    }

    public function confirmPayment(FeePayment $payment, ?User $actor = null): FeePayment
    {
        abort_unless($payment->status === 'PENDING', 422, 'Only pending payments can be confirmed.');

        return DB::transaction(function () use ($payment, $actor): FeePayment {
            $payment->status = 'CONFIRMED';
            $payment->receipt_no = $payment->receipt_no ?: $this->nextRef('RCPT');
            $payment->paid_at = now();
            if ($actor !== null) {
                $payment->recorded_by = $actor->id;
            }
            $payment->save();

            $invoice = $payment->invoice()->lockForUpdate()->firstOrFail();
            $invoice->amount_paid = (float) $invoice->amount_paid + (float) $payment->amount;
            $invoice->refreshSettlementStatus();

            return $payment->fresh(['invoice']) ?? $payment;
        });
    }

    public function money(float $amount, string $currency = 'KES'): string
    {
        return $currency.' '.number_format($amount, 0);
    }

    private function nextRef(string $prefix): string
    {
        return sprintf('%s-%s-%04d', $prefix, now()->format('ymd'), random_int(1, 9999));
    }
}
