<?php

declare(strict_types=1);

namespace App\Modules\Admission\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicationPaymentAttempt;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The only way an application fee becomes settled.
 *
 * `PaymentInitiationService` records intent; nothing it writes is money. A fee
 * is settled here and only here, from a source that can be held to account: a
 * verified provider callback, a named Finance Officer, or a reconciliation run.
 * A browser request cannot reach this class, which is what stops a redirect
 * from manufacturing a paid application.
 */
final class PaymentConfirmationService
{
    /** Where a confirmation came from. Recorded on every settlement. */
    public const SOURCE_WEBHOOK = 'provider_webhook';

    public const SOURCE_FINANCE = 'finance_manual';

    public const SOURCE_RECONCILIATION = 'reconciliation';

    public const SOURCE_SANDBOX = 'sandbox';

    /** Statuses an attempt may still move out of. */
    private const OPEN = ['INITIATED', 'PENDING', 'AWAITING_VERIFICATION', 'FAILED', 'EXPIRED'];

    public function __construct(private readonly AdmissionPipeline $pipeline) {}

    /**
     * Settle an attempt.
     *
     * Idempotent by design: a provider that retries its callback, a clerk who
     * double-clicks, and a reconciliation sweep that overlaps a webhook all
     * converge on the same single settled attempt rather than three.
     */
    public function confirm(
        ApplicationPaymentAttempt $attempt,
        string $source,
        ?int $actorId = null,
        ?string $providerReference = null,
        ?float $amount = null,
        array $providerPayload = [],
    ): ApplicationPaymentAttempt {
        return DB::transaction(function () use ($attempt, $source, $actorId, $providerReference, $amount, $providerPayload): ApplicationPaymentAttempt {
            $attempt = ApplicationPaymentAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if (in_array($attempt->status, ['PAID', 'WAIVED'], true)) {
                return $attempt;
            }

            $application = AdmissionApplication::query()->findOrFail($attempt->admission_application_id);
            $expected = (float) ($attempt->expected_amount ?? $attempt->amount);
            $settled = $amount ?? $expected;

            // An underpayment is not a payment. It is parked for Finance rather
            // than silently accepted or silently discarded.
            if ($settled + 0.001 < $expected) {
                return $this->park($attempt, $application, 'UNDERPAID', sprintf(
                    'Received %s %s against an expected %s %s.',
                    number_format($settled, 2), $attempt->currency, number_format($expected, 2), $attempt->currency,
                ), $source, $actorId, $providerReference, $settled, $providerPayload);
            }

            $from = $attempt->status;
            $attempt->forceFill([
                'status' => 'PAID',
                'amount' => (int) round($settled),
                'paid_at' => now(),
                'provider_request_ref' => $providerReference ?? $attempt->provider_request_ref,
                'receipt_number' => $attempt->receipt_number ?? $this->receiptNumber(),
                'last_verified_at' => now(),
                'verification_attempts' => (int) $attempt->verification_attempts + 1,
                'failure_code' => null,
                'failure_reason' => null,
                'provider_payload' => array_merge((array) $attempt->provider_payload, [
                    'confirmation_source' => $source,
                    'confirmed_at' => now()->toIso8601String(),
                    'confirmed_by' => $actorId,
                ], $providerPayload === [] ? [] : ['provider' => $providerPayload]),
            ])->save();

            $this->history($attempt, $application, $from, 'PAID', $source, $actorId, [
                'provider_reference' => $providerReference,
                'amount' => $settled,
            ]);

            // The attempt is the applicant's side of the story; the ledger entry
            // and receipt are what Finance reconciles against.
            $this->pipeline->recordPayment($application, $attempt, $actorId);

            $application->forceFill([
                'payment_status' => 'PAID',
                'fee_amount_expected' => $application->fee_amount_expected ?? $expected,
                'fee_currency' => $application->fee_currency ?? $attempt->currency,
                'last_activity_at' => now(),
            ])->save();

            AuditLog::record('application_fee.confirmed', $attempt, ['status' => $from], [
                'status' => 'PAID',
                'source' => $source,
                'actor_id' => $actorId,
                'provider_reference' => $providerReference,
                'amount' => $settled,
                'currency' => $attempt->currency,
            ]);

            return $attempt->refresh();
        });
    }

    /**
     * The applicant declares the code printed on their M-Pesa or bank message.
     * A declared code is a claim, not a settlement: it moves the attempt onto the
     * Finance queue and nothing more.
     */
    public function declareTransactionCode(
        ApplicationPaymentAttempt $attempt,
        string $code,
        ?string $payerName,
        ?int $actorId,
    ): ApplicationPaymentAttempt {
        if (in_array($attempt->status, ['PAID', 'WAIVED'], true)) {
            throw ValidationException::withMessages(['transaction_code' => 'This payment has already been settled.']);
        }
        if (! in_array($attempt->status, self::OPEN, true)) {
            throw ValidationException::withMessages(['transaction_code' => 'This payment can no longer be updated.']);
        }

        $code = strtoupper(trim($code));
        $duplicate = ApplicationPaymentAttempt::query()
            ->where('provider_request_ref', $code)
            ->where('id', '!=', $attempt->id)
            ->exists();
        if ($duplicate) {
            throw ValidationException::withMessages([
                'transaction_code' => 'That transaction code has already been submitted against another application.',
            ]);
        }

        $from = $attempt->status;
        $attempt->forceFill([
            'status' => 'AWAITING_VERIFICATION',
            'provider_request_ref' => $code,
            'payer_name' => $payerName,
            'provider_payload' => array_merge((array) $attempt->provider_payload, [
                'declared_code' => $code,
                'declared_at' => now()->toIso8601String(),
            ]),
        ])->save();

        $application = AdmissionApplication::query()->findOrFail($attempt->admission_application_id);
        $this->history($attempt, $application, $from, 'AWAITING_VERIFICATION', 'applicant_declaration', $actorId, ['code' => $code]);
        $application->forceFill(['payment_status' => 'AWAITING_VERIFICATION', 'last_activity_at' => now()])->save();

        AuditLog::record('application_fee.code_declared', $attempt, ['status' => $from], [
            'status' => 'AWAITING_VERIFICATION',
            'transaction_code' => $code,
        ]);

        return $attempt->refresh();
    }

    /** Record a failed, cancelled, timed-out or reversed attempt. */
    public function fail(
        ApplicationPaymentAttempt $attempt,
        string $code,
        string $reason,
        string $source,
        ?int $actorId = null,
    ): ApplicationPaymentAttempt {
        if (in_array($attempt->status, ['PAID', 'WAIVED'], true)) {
            // A settled fee is never downgraded by a late failure notice; the
            // discrepancy belongs to reconciliation, not to this path.
            AuditLog::record('application_fee.late_failure_ignored', $attempt, null, [
                'code' => $code, 'reason' => $reason, 'source' => $source,
            ]);

            return $attempt;
        }

        $from = $attempt->status;
        $attempt->forceFill([
            'status' => 'FAILED',
            'failure_code' => $code,
            'failure_reason' => Str::limit($reason, 190),
            'last_verified_at' => now(),
            'verification_attempts' => (int) $attempt->verification_attempts + 1,
        ])->save();

        $application = AdmissionApplication::query()->findOrFail($attempt->admission_application_id);
        $this->history($attempt, $application, $from, 'FAILED', $source, $actorId, ['code' => $code, 'reason' => $reason]);

        AuditLog::record('application_fee.failed', $attempt, ['status' => $from], [
            'status' => 'FAILED', 'code' => $code, 'reason' => $reason, 'source' => $source,
        ]);

        return $attempt->refresh();
    }

    /** Hold an anomalous payment for a human instead of guessing. */
    private function park(
        ApplicationPaymentAttempt $attempt,
        AdmissionApplication $application,
        string $code,
        string $reason,
        string $source,
        ?int $actorId,
        ?string $providerReference,
        float $settled,
        array $providerPayload,
    ): ApplicationPaymentAttempt {
        $from = $attempt->status;
        $attempt->forceFill([
            'status' => 'AWAITING_VERIFICATION',
            'failure_code' => $code,
            'failure_reason' => Str::limit($reason, 190),
            'provider_request_ref' => $providerReference ?? $attempt->provider_request_ref,
            'last_verified_at' => now(),
            'verification_attempts' => (int) $attempt->verification_attempts + 1,
            'provider_payload' => array_merge((array) $attempt->provider_payload, [
                'anomaly' => $code,
                'received_amount' => $settled,
                'provider' => $providerPayload,
            ]),
        ])->save();

        $this->history($attempt, $application, $from, 'AWAITING_VERIFICATION', $source, $actorId, [
            'anomaly' => $code, 'received_amount' => $settled,
        ]);

        AuditLog::record('application_fee.anomaly', $attempt, ['status' => $from], [
            'code' => $code, 'reason' => $reason, 'source' => $source,
        ]);

        return $attempt->refresh();
    }

    private function history(
        ApplicationPaymentAttempt $attempt,
        AdmissionApplication $application,
        ?string $from,
        string $to,
        string $source,
        ?int $actorId,
        array $metadata = [],
    ): void {
        DB::table('payment_status_history')->insert([
            'admission_application_id' => $application->id,
            'application_payment_attempt_id' => $attempt->id,
            'from_status' => $from,
            'to_status' => $to,
            'reason_code' => $source,
            'actor_user_id' => $actorId,
            'actor_role' => $actorId === null ? 'system' : User::find($actorId)?->role,
            'source_channel' => $attempt->channel,
            'correlation_id' => $attempt->correlation_id,
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }

    private function receiptNumber(): string
    {
        return 'MEMA-RCPT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}
