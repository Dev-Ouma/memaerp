<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FeeStructure;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Payment;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class FinanceService
{
    public function __construct(private readonly ClearanceService $clearance) {}

    /** @return array<string, mixed> */
    public function statement(string $institutionId, string $personId): array
    {
        $invoices = Invoice::query()
            ->where('institution_id', $institutionId)
            ->where('person_id', $personId)
            ->with(['term', 'feeStructure', 'payments'])
            ->orderByDesc('due_date')
            ->get();

        $payments = Payment::query()
            ->where('institution_id', $institutionId)
            ->where('person_id', $personId)
            ->orderByDesc('paid_at')
            ->get();

        return [
            'invoices' => $invoices,
            'payments' => $payments,
            'clearance' => $this->clearance->forPerson($institutionId, $personId),
        ];
    }

    public function issueTermInvoice(Student $student, Term $term): Invoice
    {
        $feeStructure = FeeStructure::query()
            ->where('institution_id', $student->institution_id)
            ->where('programme_id', $student->programme_id)
            ->where('year_level', $student->current_year_level)
            ->where('semester', $student->current_semester)
            ->where('is_active', true)
            ->first();

        if ($feeStructure === null) {
            throw ValidationException::withMessages([
                'fee_structure' => ['No active fee structure exists for this student programme and year level.'],
            ]);
        }

        return Invoice::query()->firstOrCreate(
            [
                'institution_id' => $student->institution_id,
                'person_id' => $student->person_id,
                'term_id' => $term->id,
            ],
            [
                'fee_structure_id' => $feeStructure->id,
                'invoice_number' => $this->nextInvoiceNumber($student->institution_id),
                'amount_due' => $feeStructure->total_amount,
                'amount_paid' => 0,
                'balance' => $feeStructure->total_amount,
                'status' => 'PENDING',
                'due_date' => $term->fee_payment_closes_at?->toDateString() ?? now()->addMonth()->toDateString(),
            ],
        );
    }

    public function recordPayment(
        Invoice $invoice,
        float $amount,
        string $method,
        ?string $transactionReference = null,
    ): Payment {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['Payment amount must be positive.']]);
        }

        return DB::transaction(function () use ($invoice, $amount, $method, $transactionReference): Payment {
            $reference = $transactionReference ?: 'PAY-'.strtoupper(Str::random(10));

            if (Payment::query()->where('transaction_reference', $reference)->exists()) {
                return Payment::query()->where('transaction_reference', $reference)->firstOrFail();
            }

            $payment = Payment::query()->create([
                'institution_id' => $invoice->institution_id,
                'invoice_id' => $invoice->id,
                'person_id' => $invoice->person_id,
                'receipt_number' => 'RCT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'payment_method' => strtoupper($method),
                'transaction_reference' => $reference,
                'amount' => $amount,
                'currency' => 'KES',
                'status' => 'COMPLETED',
                'paid_at' => now(),
            ]);

            $paid = min((float) $invoice->amount_due, (float) $invoice->amount_paid + $amount);
            $balance = max(0, (float) $invoice->amount_due - $paid);
            $status = match (true) {
                $balance <= 0 => 'FULLY_PAID',
                $paid > 0 => 'PARTIALLY_PAID',
                default => 'PENDING',
            };

            $invoice->forceFill([
                'amount_paid' => $paid,
                'balance' => $balance,
                'status' => $status,
            ])->save();

            return $payment;
        });
    }

    /** @param array<string, mixed> $payload */
    public function ingestMpesaCallback(array $payload): Payment
    {
        $reference = (string) ($payload['TransID'] ?? $payload['transaction_reference'] ?? '');
        $amount = (float) ($payload['TransAmount'] ?? $payload['amount'] ?? 0);
        $account = (string) ($payload['BillRefNumber'] ?? $payload['invoice_number'] ?? '');

        if ($reference === '' || $amount <= 0 || $account === '') {
            throw ValidationException::withMessages(['callback' => ['Invalid M-Pesa callback payload.']]);
        }

        $invoice = Invoice::query()->where('invoice_number', $account)->firstOrFail();

        return $this->recordPayment($invoice, $amount, 'MPESA', $reference);
    }

    public function initiateMpesaStk(Invoice $invoice, string $phone, float $amount): array
    {
        $payment = $this->recordPayment($invoice, $amount, 'MPESA', 'STK-'.strtoupper(Str::random(12)));

        return [
            'status' => 'COMPLETED',
            'checkout_request_id' => 'ws_CO_'.Str::random(10),
            'message' => 'Simulated STK push completed successfully.',
            'payment_id' => $payment->id,
            'receipt_number' => $payment->receipt_number,
        ];
    }

    public function receiptPdf(Payment $payment): Response
    {
        $payment->load(['invoice.term', 'person']);

        return Pdf::loadView('reports.payment-receipt', ['payment' => $payment])
            ->setPaper('a4')
            ->download('receipt-'.$payment->receipt_number.'.pdf');
    }

    private function nextInvoiceNumber(string $institutionId): string
    {
        $count = Invoice::query()->where('institution_id', $institutionId)->count() + 1;

        return sprintf('INV-%s-%05d', now()->format('Y'), $count);
    }
}
