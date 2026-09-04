<?php

declare(strict_types=1);

namespace App\Modules\Admission\Http\Controllers;

use App\Models\ApplicationPaymentAttempt;
use App\Modules\Admission\Services\PaymentConfirmationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Provider callbacks — the only automatic route to a settled application fee.
 *
 * Three things are true of every request handled here, and the order matters:
 * the caller is authenticated by signature and source address before its body is
 * read; the delivery is recorded in `payment_provider_events` before it is acted
 * on, which is what makes a retried callback idempotent; and the response is a
 * bare acknowledgement, because a provider must never learn from our reply
 * whether a reference exists.
 */
final class PaymentWebhookController
{
    public function __construct(private readonly PaymentConfirmationService $payments) {}

    /** M-Pesa STK Push result callback. */
    public function mpesaStk(Request $request): JsonResponse
    {
        return $this->handle($request, 'mpesa_stk', function (array $payload): array {
            $body = data_get($payload, 'Body.stkCallback', $payload);
            $reference = (string) (data_get($body, 'MerchantRequestID') ?? data_get($body, 'CheckoutRequestID') ?? '');
            $resultCode = (int) data_get($body, 'ResultCode', 1);
            $items = collect((array) data_get($body, 'CallbackMetadata.Item', []))
                ->mapWithKeys(fn ($item): array => [(string) data_get($item, 'Name') => data_get($item, 'Value')]);

            return [
                'reference' => $reference,
                'success' => $resultCode === 0,
                'provider_reference' => (string) ($items['MpesaReceiptNumber'] ?? $reference),
                'amount' => $items['Amount'] === null ? null : (float) $items['Amount'],
                'failure_code' => 'MPESA_'.$resultCode,
                'failure_reason' => (string) data_get($body, 'ResultDesc', 'The M-Pesa request did not complete.'),
            ];
        });
    }

    /** M-Pesa C2B confirmation for paybill, till and Pochi payments. */
    public function mpesaC2b(Request $request): JsonResponse
    {
        return $this->handle($request, 'mpesa_c2b', function (array $payload): array {
            // C2B carries the applicant reference in the account number field,
            // which is what the payment instructions tell applicants to enter.
            $account = trim((string) (data_get($payload, 'BillRefNumber') ?? data_get($payload, 'InvoiceNumber') ?? ''));

            return [
                'reference' => $account,
                'match_by' => 'application',
                'success' => true,
                'provider_reference' => (string) data_get($payload, 'TransID', ''),
                'amount' => (float) data_get($payload, 'TransAmount', 0),
                'failure_code' => 'MPESA_C2B',
                'failure_reason' => 'The C2B confirmation could not be applied.',
            ];
        });
    }

    /** Card provider webhook (Stripe-shaped payment intent events). */
    public function card(Request $request): JsonResponse
    {
        return $this->handle($request, 'card', function (array $payload): array {
            $object = (array) data_get($payload, 'data.object', $payload);
            $type = (string) data_get($payload, 'type', '');

            return [
                'reference' => (string) (data_get($object, 'metadata.payment_reference') ?? data_get($object, 'client_reference_id') ?? ''),
                'success' => in_array($type, ['payment_intent.succeeded', 'checkout.session.completed'], true),
                'provider_reference' => (string) (data_get($object, 'id') ?? ''),
                'amount' => data_get($object, 'amount_received') === null ? null : ((float) data_get($object, 'amount_received')) / 100,
                'failure_code' => 'CARD_'.strtoupper(str_replace('.', '_', $type ?: 'UNKNOWN')),
                'failure_reason' => (string) (data_get($object, 'last_payment_error.message') ?? 'The card payment did not complete.'),
            ];
        });
    }

    /**
     * @param  callable(array):array  $parser
     */
    private function handle(Request $request, string $provider, callable $parser): JsonResponse
    {
        $config = (array) config("admission.payments.providers.{$provider}", []);

        if (! (bool) ($config['enabled'] ?? false)) {
            return $this->ack();
        }
        if (! $this->sourceAllowed($request, $config)) {
            $this->reject($provider, $request, 'source_not_allowed');

            return $this->ack();
        }
        if (! $this->signatureValid($request, $config)) {
            $this->reject($provider, $request, 'signature_invalid');

            return $this->ack();
        }

        $payload = (array) $request->json()->all();
        $eventId = $this->eventId($request, $provider, $payload);

        // Recording the delivery first is what makes a retry a no-op: the unique
        // index on (provider, provider_event_id) rejects the second insert before
        // any money-moving work is reached.
        $inserted = DB::table('payment_provider_events')->insertOrIgnore([
            'id' => (string) Str::uuid(),
            'provider' => $provider,
            'provider_event_id' => $eventId,
            'event_type' => (string) ($request->input('type') ?? $provider),
            'received_at' => now(),
            'signature' => Str::limit((string) $request->header('X-Signature', ''), 190, ''),
            'signature_algorithm' => 'hmac-sha256',
            'signature_verified' => true,
            'payload_hash' => hash('sha256', $request->getContent()),
            'processing_status' => 'RECEIVED',
            'ip_address' => $request->ip(),
            'correlation_id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted === 0) {
            return $this->ack();
        }

        $parsed = $parser($payload);
        $attempt = $this->resolveAttempt($parsed);

        if ($attempt === null) {
            $this->finish($provider, $eventId, 'UNMATCHED', 'no_attempt_for_reference');

            return $this->ack();
        }

        if ($parsed['success']) {
            $this->payments->confirm(
                $attempt,
                PaymentConfirmationService::SOURCE_WEBHOOK,
                null,
                $parsed['provider_reference'] ?: null,
                $parsed['amount'],
                $payload,
            );
        } else {
            $this->payments->fail(
                $attempt,
                $parsed['failure_code'],
                $parsed['failure_reason'],
                PaymentConfirmationService::SOURCE_WEBHOOK,
            );
        }

        $this->finish($provider, $eventId, 'PROCESSED', null);

        return $this->ack();
    }

    /**
     * Find the attempt a callback belongs to, either by our own payment
     * reference or — for C2B, where the payer types it — by application number.
     */
    private function resolveAttempt(array $parsed): ?ApplicationPaymentAttempt
    {
        $reference = trim((string) ($parsed['reference'] ?? ''));
        if ($reference === '') {
            return null;
        }

        $attempt = ApplicationPaymentAttempt::query()
            ->where('reference', $reference)
            ->orWhere('provider_request_ref', $reference)
            ->first();
        if ($attempt !== null) {
            return $attempt;
        }

        if (($parsed['match_by'] ?? null) !== 'application') {
            return null;
        }

        $applicationId = DB::table('admission_applications')
            ->where('application_number', $reference)
            ->value('id');
        if ($applicationId === null) {
            $applicationId = DB::table('admission_applications as a')
                ->join('applicant_profiles as p', 'p.id', '=', 'a.applicant_profile_id')
                ->where('p.applicant_number', $reference)
                ->orderByDesc('a.created_at')
                ->value('a.id');
        }

        return $applicationId === null ? null : ApplicationPaymentAttempt::query()
            ->where('admission_application_id', $applicationId)
            ->whereNotIn('status', ['PAID', 'WAIVED'])
            ->latest()
            ->first();
    }

    /** Constant-time HMAC over the raw body, keyed by the provider's shared secret. */
    private function signatureValid(Request $request, array $config): bool
    {
        $secret = (string) ($config['callback_secret'] ?? '');
        if ($secret === '') {
            // Without a configured secret the callback cannot be authenticated,
            // so it is refused rather than trusted.
            return false;
        }

        $provided = (string) ($request->header('X-Signature') ?? $request->header('Stripe-Signature') ?? '');
        if ($provided === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $request->getContent(), $secret), $provided);
    }

    private function sourceAllowed(Request $request, array $config): bool
    {
        $allowed = array_filter((array) ($config['allowed_ips'] ?? []));

        return $allowed === [] || in_array((string) $request->ip(), array_map('trim', $allowed), true);
    }

    private function eventId(Request $request, string $provider, array $payload): string
    {
        $candidate = (string) (
            data_get($payload, 'id')
            ?? data_get($payload, 'TransID')
            ?? data_get($payload, 'Body.stkCallback.CheckoutRequestID')
            ?? ''
        );

        return $candidate !== '' ? $candidate : $provider.':'.hash('sha256', $request->getContent());
    }

    private function finish(string $provider, string $eventId, string $status, ?string $error): void
    {
        DB::table('payment_provider_events')
            ->where('provider', $provider)
            ->where('provider_event_id', $eventId)
            ->update([
                'processing_status' => $status,
                'processed_at' => now(),
                'error_code' => $error,
                'updated_at' => now(),
            ]);
    }

    private function reject(string $provider, Request $request, string $reason): void
    {
        Log::warning('Rejected payment webhook', [
            'provider' => $provider,
            'reason' => $reason,
            'ip' => $request->ip(),
        ]);
    }

    /**
     * Providers retry anything that is not a 200, and several treat a body as
     * significant. An opaque acknowledgement keeps both concerns quiet without
     * disclosing whether the reference matched anything.
     */
    private function ack(): JsonResponse
    {
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
