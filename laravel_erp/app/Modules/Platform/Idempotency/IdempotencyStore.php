<?php

declare(strict_types=1);

namespace App\Modules\Platform\Idempotency;

use App\Modules\Platform\Api\ApiException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Exactly-once semantics for unsafe requests.
 *
 * A retried payment initiation or application submission must not create a second charge or a second
 * submission. The first request claims the key by inserting a row; a retry with the same key and the
 * same body gets the original response replayed, and a retry with a *different* body is rejected
 * outright rather than quietly doing something new under an old key.
 */
final class IdempotencyStore
{
    public const RETENTION_HOURS = 24;

    /**
     * @return array{status: string, replay?: array{status: int, body: array<string, mixed>}}
     */
    public function begin(string $key, string $route, string $principal, string $requestHash): array
    {
        $now = now();

        $claimed = DB::table('idempotency_keys')->insertOrIgnore([
            'id' => (string) Str::uuid(),
            'idempotency_key' => $key,
            'route' => $route,
            'principal' => $principal,
            'request_hash' => $requestHash,
            'locked_at' => $now,
            'expires_at' => $now->copy()->addHours(self::RETENTION_HOURS),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($claimed === 1) {
            return ['status' => 'claimed'];
        }

        $existing = DB::table('idempotency_keys')
            ->where('idempotency_key', $key)
            ->where('route', $route)
            ->where('principal', $principal)
            ->first();

        if ($existing === null) {
            // The key belongs to a different route or principal; treat it as taken.
            throw ApiException::conflict(
                'IDEMPOTENCY_KEY_IN_USE',
                'That idempotency key has already been used.',
                'Generate a new key for a new request.',
            );
        }

        if (! hash_equals((string) $existing->request_hash, $requestHash)) {
            throw ApiException::unprocessable(
                'IDEMPOTENCY_KEY_REUSED',
                'This idempotency key was already used with a different request body.',
                ['Idempotency-Key' => ['Reuse the original body to replay, or send a new key for a new request.']],
            );
        }

        if ($existing->completed_at !== null) {
            return [
                'status' => 'replay',
                'replay' => [
                    'status' => (int) $existing->response_status,
                    'body' => json_decode((string) $existing->response_body, true) ?: [],
                ],
            ];
        }

        throw ApiException::conflict(
            'REQUEST_IN_PROGRESS',
            'An identical request is still being processed.',
            'Retry in a few seconds; the original request has not finished.',
        );
    }

    /** @param array<string, mixed>|null $body */
    public function complete(string $key, string $route, string $principal, int $status, ?array $body): void
    {
        DB::table('idempotency_keys')
            ->where('idempotency_key', $key)
            ->where('route', $route)
            ->where('principal', $principal)
            ->update([
                'response_status' => $status,
                'response_body' => $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR),
                'completed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /** Releases a claim so the caller can genuinely retry after a failure. */
    public function release(string $key, string $route, string $principal): void
    {
        DB::table('idempotency_keys')
            ->where('idempotency_key', $key)
            ->where('route', $route)
            ->where('principal', $principal)
            ->whereNull('completed_at')
            ->delete();
    }

    public function purgeExpired(): int
    {
        return DB::table('idempotency_keys')->where('expires_at', '<', now())->delete();
    }

    /** @param array<string, mixed> $payload */
    public static function hashRequest(array $payload): string
    {
        ksort($payload);

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
