<?php

declare(strict_types=1);

namespace App\Modules\Iam\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Whoever is performing the action under way.
 *
 * The platform layer needs to attribute work to somebody without knowing what an IAM user is —
 * that is the entire reason this interface exists. It is deliberately tiny: an identity, a label
 * fit to appear in an audit trail, and the institution the actor belongs to. Anything richer
 * belongs on the concrete model, behind a module boundary.
 */
interface Actor extends Authenticatable
{
    /**
     * A human-readable identifier, denormalised into the audit trail at write time.
     *
     * The trail must stay readable after the account is renamed, deactivated or deleted, so this
     * value is copied, never joined to.
     */
    public function auditLabel(): string;

    /** The institution this actor acts within. */
    public function actorInstitutionId(): ?string;
}
