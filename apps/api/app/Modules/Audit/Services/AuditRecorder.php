<?php

declare(strict_types=1);

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Contracts\AuditRecorder as AuditRecorderContract;
use App\Modules\Iam\Contracts\Actor;
use App\Platform\Support\RequestContext;
use App\Platform\Support\Uuid7;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Writes audit records.
 *
 * Two rules govern this class, and both are load-bearing:
 *
 * 1. **The audit row is written in the SAME transaction as the change it describes.** Not in a
 *    queued job, not in an `after commit` hook. If the business change commits, its audit record
 *    commits with it; if the audit write fails, the business change rolls back too. An audit
 *    trail that can be missing entries for successful changes is not evidence of anything.
 *
 * 2. **It inserts through the query builder, not Eloquent.** Eloquent model events on the audit
 *    table would be recursive, and the table has no updated_at because rows are never updated.
 *
 * Side effects that must NOT hold up the transaction — notifications, webhooks, search indexing —
 * belong after commit. Audit does not.
 */
final readonly class AuditRecorder implements AuditRecorderContract
{
    public function __construct(
        private ConnectionInterface $connection,
        private RequestContext $context,
    ) {}

    /**
     * Record a change to a model.
     *
     * @param array<string, mixed>|null $old
     * @param array<string, mixed>|null $new
     */
    public function record(
        string $event,
        Model $auditable,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
        ?Actor $actor = null,
    ): string {
        $institutionId = $auditable->getAttribute('institution_id')
            ?? $this->context->institutionId();

        if ($institutionId === null) {
            throw new RuntimeException(sprintf(
                'Cannot audit %s: no institution could be determined. Every audit record must be '
                .'attributable to an institution.',
                $auditable::class,
            ));
        }

        $changed = $this->changedColumns($old, $new);

        return $this->write([
            'institution_id' => $institutionId,
            'event' => $event,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'module' => $this->moduleOf($auditable::class),
            'old_values' => $old === null ? null : json_encode($old, JSON_THROW_ON_ERROR),
            'new_values' => $new === null ? null : json_encode($new, JSON_THROW_ON_ERROR),
            'changed_columns' => $changed === [] ? null : '{'.implode(',', $changed).'}',
            'reason' => $reason,
        ], $actor);
    }

    /**
     * Record something that is not a model change — a sign-in, a denied permission, an export.
     *
     * @param array<string, mixed> $details
     */
    public function recordEvent(
        string $event,
        string $module,
        array $details = [],
        ?string $reason = null,
        ?Actor $actor = null,
        ?string $institutionId = null,
    ): string {
        $institutionId ??= $this->context->institutionId();

        if ($institutionId === null) {
            throw new RuntimeException("Cannot audit event [{$event}]: no institution in context.");
        }

        return $this->write([
            'institution_id' => $institutionId,
            'event' => $event,
            'auditable_type' => 'event',
            'auditable_id' => null,
            'module' => $module,
            'old_values' => null,
            'new_values' => $details === [] ? null : json_encode($details, JSON_THROW_ON_ERROR),
            'changed_columns' => null,
            'reason' => $reason,
        ], $actor);
    }

    /** @param array<string, mixed> $attributes */
    private function write(array $attributes, ?Actor $actor): string
    {
        $actor ??= $this->context->actor();
        $id = Uuid7::generate();

        $this->connection->table('audit.activity_log')->insert([
            'id' => $id,
            'occurred_at' => now(),

            'actor_id' => $actor?->getKey(),
            // Denormalised so the trail stays readable after an account is renamed or removed.
            'actor_label' => $actor?->auditLabel(),
            'on_behalf_of_id' => $this->context->impersonatedUserId(),
            'impersonation_id' => $this->context->impersonationSessionId(),

            'ip_address' => $this->context->ipAddress(),
            'user_agent' => $this->context->userAgent(),
            'correlation_id' => $this->context->correlationId(),
            'request_id' => $this->context->requestId(),

            ...$attributes,
        ]);

        return $id;
    }

    /**
     * @param array<string, mixed>|null $old
     * @param array<string, mixed>|null $new
     * @return list<string>
     */
    private function changedColumns(?array $old, ?array $new): array
    {
        if ($old === null || $new === null) {
            return array_keys($new ?? $old ?? []);
        }

        $changed = [];

        foreach ($new as $column => $value) {
            if (! array_key_exists($column, $old) || $old[$column] !== $value) {
                $changed[] = $column;
            }
        }

        return $changed;
    }

    /**
     * Derive the owning module from the model's namespace, so `module` is never wrong because
     * somebody forgot to pass it. App\Modules\Finance\Models\Invoice -> finance.
     */
    private function moduleOf(string $class): string
    {
        if (preg_match('/^App\\\\Modules\\\\([^\\\\]+)\\\\/', $class, $matches) === 1) {
            return strtolower($matches[1]);
        }

        return 'platform';
    }
}
