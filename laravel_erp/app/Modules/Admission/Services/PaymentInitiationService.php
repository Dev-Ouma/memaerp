<?php

declare(strict_types=1);

namespace App\Modules\Admission\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicationPaymentAttempt;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PaymentInitiationService
{
    public function __construct(
        private readonly AdmissionPipeline $pipeline,
        private readonly PaymentConfirmationService $confirmations,
    ) {}

    public function initiate(AdmissionApplication $application, string $channel, ?string $phone, int $actorId): ApplicationPaymentAttempt
    {
        $provider = $this->provider($channel);
        $configured = (array) config("admission.payments.providers.{$provider}", []);
        $sandbox = (bool) config('admission.payments.sandbox_auto_confirm', false);

        if (! $sandbox && ! (bool) ($configured['enabled'] ?? false)) {
            throw ValidationException::withMessages(['channel' => 'This payment method is not currently available.']);
        }

        if ($provider === 'mpesa_stk' && blank($phone)) {
            throw ValidationException::withMessages(['phone' => 'Enter the M-Pesa phone number that should receive the STK prompt.']);
        }

        $fee = DB::table('payment_fee_setups')
            ->where('is_active', true)
            ->where('code', 'APPLICATION_FEE')
            ->whereDate('effective_from', '<=', today())
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', today()))
            ->where(fn ($query) => $query->whereNull('programme_offering_id')->orWhere('programme_offering_id', $application->programme_offering_id))
            ->orderByDesc('programme_offering_id')
            ->orderByDesc('effective_from')
            ->first();

        $amount = (float) ($fee->amount ?? config('admission.fee.amount'));
        $currency = (string) ($fee->currency ?? config('admission.fee.currency'));
        $status = 'INITIATED';
        $reference = 'PAY-'.strtoupper(Str::random(12));
        $maskedPhone = $phone === null ? null : str_repeat('*', max(strlen($phone) - 4, 0)).substr($phone, -4);

        $attempt = ApplicationPaymentAttempt::create([
            'admission_application_id' => $application->id,
            'reference' => $reference,
            'provider_request_ref' => $sandbox ? 'SANDBOX-'.$reference : null,
            'channel' => $channel,
            'provider' => strtoupper($provider),
            'amount' => $amount,
            'expected_amount' => $amount,
            'currency' => $currency,
            'status' => $status,
            'idempotency_key' => (string) Str::uuid(),
            'payer_msisdn_masked' => $maskedPhone,
            'expires_at' => now()->addMinutes(15),
            'correlation_id' => (string) Str::uuid(),
            'created_by' => $actorId,
            'provider_payload' => ['mode' => $sandbox ? 'test' : 'provider_pending'],
            'institution_id' => $application->institution_id ?? null,
            'fee_setup_id' => $fee->id ?? null,
        ]);

        $application->forceFill([
            'fee_amount_expected' => $amount,
            'fee_currency' => $currency,
        ])->save();

        AuditLog::record('application_fee.initiated', $attempt, null, [
            'application_id' => $application->id,
            'provider' => $attempt->provider,
            'status' => $status,
            'amount' => $amount,
            'currency' => $currency,
        ]);

        if ($sandbox) {
            // Test convenience only, and it still travels the single settlement
            // path so a sandbox payment is indistinguishable in the ledger from
            // a real one except for its recorded source.
            return $this->confirmations->confirm(
                $attempt,
                PaymentConfirmationService::SOURCE_SANDBOX,
                $actorId,
                'SANDBOX-'.$reference,
                $amount,
            );
        }

        return $attempt;
    }

    private function provider(string $channel): string
    {
        return match ($channel) {
            'mpesa' => 'mpesa_stk',
            'paybill', 'till', 'pochi' => 'mpesa_c2b',
            'card', 'stripe' => 'card',
            'bank' => 'bank_transfer',
            'cashier' => 'cashier',
            default => throw ValidationException::withMessages(['channel' => 'Unsupported payment method.']),
        };
    }
}
