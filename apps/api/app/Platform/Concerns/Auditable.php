<?php

declare(strict_types=1);

namespace App\Platform\Concerns;

use App\Modules\Audit\Contracts\AuditEvent;
use App\Modules\Audit\Contracts\AuditRecorder;
use Illuminate\Database\Eloquent\Model;

/**
 * Records every create, update and delete of a model to the audit trail.
 *
 * Uses `created`/`updated`/`deleted` — NOT the `*ed` after-commit variants — so the audit row
 * joins the caller's transaction. If the surrounding transaction rolls back, the audit row goes
 * with it, which is correct: nothing happened, so nothing should be claimed to have happened.
 *
 * Models opt IN. Blanket auditing of every table produces a trail nobody reads and a write
 * amplification nobody budgeted for; audit what matters — money, grades, identity, permissions.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model): void {
            $model->recordAudit(AuditEvent::CREATED, null, $model->auditableAttributes());
        });

        static::updated(function (self $model): void {
            $changes = $model->auditableChanges();

            // A save that changed nothing auditable is not an event.
            if ($changes['new'] === []) {
                return;
            }

            $model->recordAudit(AuditEvent::UPDATED, $changes['old'], $changes['new']);
        });

        static::deleted(function (self $model): void {
            $model->recordAudit(AuditEvent::DELETED, $model->auditableAttributes(), null);
        });
    }

    /**
     * Attributes excluded from the trail. Override per model to add more.
     *
     * Secrets are excluded because an audit trail is read by auditors, support staff and
     * regulators — writing a password hash or an MFA secret into it hands those to a much wider
     * audience than the table they came from.
     *
     * @return list<string>
     */
    public function auditExcluded(): array
    {
        return [
            'password', 'remember_token', 'mfa_secret', 'mfa_recovery_codes',
            'created_at', 'updated_at',
        ];
    }

    /** @return array<string, mixed> */
    public function auditableAttributes(): array
    {
        return array_diff_key($this->attributesToArray(), array_flip($this->auditExcluded()));
    }

    /** @return array{old: array<string, mixed>, new: array<string, mixed>} */
    public function auditableChanges(): array
    {
        $new = array_diff_key($this->getChanges(), array_flip($this->auditExcluded()));
        $old = array_intersect_key($this->getOriginal(), $new);

        return ['old' => $old, 'new' => $new];
    }

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public function recordAudit(string $event, ?array $old, ?array $new, ?string $reason = null): void
    {
        app(AuditRecorder::class)->record($event, $this, $old, $new, $reason);
    }
}
