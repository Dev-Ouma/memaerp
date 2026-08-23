<?php

declare(strict_types=1);

namespace App\Modules\Audit\Models;

use App\Modules\Audit\Contracts\AuditEvent;
use App\Modules\Audit\Services\AuditRecorder;
use App\Platform\Concerns\HasUuid7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * A read model over the append-only audit trail.
 *
 * Writes do NOT go through this class — {@see AuditRecorder} inserts
 * directly, because it must participate in the caller's transaction without Eloquent events
 * firing more audit records. Update and delete are blocked here as well as in the database; the
 * database trigger is the real guarantee, this is just an earlier, clearer error.
 */
final class ActivityLog extends Model
{
    use HasUuid7;

    public const string EVENT_CREATED = AuditEvent::CREATED;

    public const string EVENT_UPDATED = AuditEvent::UPDATED;

    public const string EVENT_DELETED = AuditEvent::DELETED;

    public const string EVENT_VIEWED = AuditEvent::VIEWED;

    public const string EVENT_EXPORTED = AuditEvent::EXPORTED;

    public const string EVENT_LOGIN = AuditEvent::LOGIN;

    public const string EVENT_LOGIN_FAILED = AuditEvent::LOGIN_FAILED;

    public const string EVENT_LOGOUT = AuditEvent::LOGOUT;

    public const string EVENT_PERMISSION_DENIED = AuditEvent::PERMISSION_DENIED;

    public const string EVENT_IMPERSONATION_STARTED = AuditEvent::IMPERSONATION_STARTED;

    public const string EVENT_IMPERSONATION_ENDED = AuditEvent::IMPERSONATION_ENDED;

    protected $table = 'audit.activity_log';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'immutable_datetime',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        throw new LogicException('The audit trail is append-only; activity log entries cannot be updated.');
    }

    public function delete(): bool
    {
        throw new LogicException('The audit trail is append-only; activity log entries cannot be deleted.');
    }

    /** @param Builder<$this> $query */
    public function scopeFor(Builder $query, Model $record): void
    {
        $query->where('auditable_type', $record::class)
            ->where('auditable_id', $record->getKey());
    }

    /** @param Builder<$this> $query */
    public function scopeByActor(Builder $query, string $userId): void
    {
        $query->where('actor_id', $userId);
    }

    /**
     * Everything that happened under one request or job — the thread that turns a list of rows
     * into a story when someone asks what actually happened.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeInCorrelation(Builder $query, string $correlationId): void
    {
        $query->where('correlation_id', $correlationId)->orderBy('occurred_at');
    }
}
