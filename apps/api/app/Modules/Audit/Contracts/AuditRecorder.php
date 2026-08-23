<?php

declare(strict_types=1);

namespace App\Modules\Audit\Contracts;

use App\Modules\Iam\Contracts\Actor;
use Illuminate\Database\Eloquent\Model;

/**
 * The audit module's write-side public API.
 *
 * Published as a contract so that the platform layer and every other module can record evidence
 * without importing the audit module's internals. Implementations MUST write inside the caller's
 * transaction: an audit trail that can be missing entries for successful changes proves nothing.
 *
 * @see \App\Modules\Audit\Services\AuditRecorder
 */
interface AuditRecorder
{
    /**
     * Record a change to a model. Returns the id of the audit entry.
     *
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public function record(
        string $event,
        Model $auditable,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
        ?Actor $actor = null,
    ): string;

    /**
     * Record something that is not a model change — a sign-in, a denied permission, an export.
     *
     * @param  array<string, mixed>  $details
     */
    public function recordEvent(
        string $event,
        string $module,
        array $details = [],
        ?string $reason = null,
        ?Actor $actor = null,
        ?string $institutionId = null,
    ): string;
}
