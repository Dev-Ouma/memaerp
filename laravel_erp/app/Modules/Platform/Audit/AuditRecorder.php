<?php

declare(strict_types=1);

namespace App\Modules\Platform\Audit;

use App\Models\Platform\AuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Append-only, tamper-evident audit trail.
 *
 * Each row stores `sha256(previous_hash || canonical payload)`, so removing or editing any row breaks
 * the chain from that point onward and the break is detectable by replaying the hashes. The database
 * additionally rejects UPDATE and DELETE on the table.
 *
 * Appends are serialised by a PostgreSQL advisory lock. That is a deliberate throughput trade for a
 * verifiable chain; at admission volumes (thousands of events per intake, not millions per hour) the
 * contention is immaterial. `docs/admission/RUNBOOKS.md` records the trade-off.
 */
final class AuditRecorder
{
    private const LOCK_KEY = 8_140_073_251;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function record(string $action, array $attributes = []): AuditEvent
    {
        $write = function () use ($action, $attributes): AuditEvent {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement('select pg_advisory_xact_lock(?)', [self::LOCK_KEY]);
            }

            $previous = AuditEvent::query()->orderByDesc('sequence_no')->first();
            $sequenceNo = ($previous?->sequence_no ?? 0) + 1;

            $payload = [
                'id' => (string) Str::uuid(),
                'sequence_no' => $sequenceNo,
                'occurred_at' => now()->toIso8601String(),
                'actor_user_id' => $attributes['actor_user_id'] ?? auth()->id(),
                'actor_role' => $attributes['actor_role'] ?? null,
                'action' => $action,
                'subject_type' => $attributes['subject_type'] ?? null,
                'subject_id' => isset($attributes['subject_id']) ? (string) $attributes['subject_id'] : null,
                'institution_id' => $attributes['institution_id'] ?? null,
                'correlation_id' => $attributes['correlation_id'] ?? correlation_id(),
                'source_channel' => $attributes['source_channel'] ?? (app()->runningInConsole() ? 'console' : 'api'),
                'ip_address' => $attributes['ip_address'] ?? request()?->ip(),
                'user_agent' => Str::limit((string) ($attributes['user_agent'] ?? request()?->userAgent()), 250, ''),
                'before' => $attributes['before'] ?? null,
                'after' => $attributes['after'] ?? null,
                'classification' => $attributes['classification'] ?? 'internal',
                'previous_hash' => $previous?->evidence_hash,
            ];

            $payload['evidence_hash'] = hash(
                'sha256',
                ($previous?->evidence_hash ?? '').'|'.json_encode($payload, JSON_THROW_ON_ERROR),
            );

            $event = new AuditEvent;
            $event->forceFill($payload)->save();

            return $event;
        };

        return DB::transactionLevel() > 0 ? $write() : DB::transaction($write);
    }

    /**
     * Replays the chain and returns the sequence number of the first inconsistent row, or null when the
     * trail verifies. Used by `admission:verify-audit-chain` and by the audit integrity test.
     */
    public function firstBrokenSequence(): ?int
    {
        $previousHash = null;

        foreach (AuditEvent::query()->orderBy('sequence_no')->cursor() as $event) {
            if ($event->previous_hash !== $previousHash) {
                return (int) $event->sequence_no;
            }
            $previousHash = $event->evidence_hash;
        }

        return null;
    }
}
