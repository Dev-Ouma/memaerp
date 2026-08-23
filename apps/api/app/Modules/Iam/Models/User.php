<?php

declare(strict_types=1);

namespace App\Modules\Iam\Models;

use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Person;
use App\Platform\Concerns\Auditable;
use App\Platform\Concerns\HasUuid7;
use App\Platform\Support\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * A login credential. NOT a human being — that is {@see Person}.
 *
 * The separation is deliberate. A person may hold several institutional identities over time
 * (applicant, then student, then employee) while keeping one login; and a person may exist with
 * no login at all, which is the normal state for a migrated historical graduate. Merging the two
 * concepts is what forces institutions to create "j.doe2@" accounts and lose the thread.
 *
 * @property-read Collection<int, RoleAssignment> $roleAssignments
 */
final class User extends Authenticatable
{
    use Auditable;
    use HasApiTokens;
    use HasFactory;
    use HasUuid7;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'iam.users';

    /**
     * A strict allow-list, not a convenience list. Authentication state — failed_login_attempts,
     * locked_until, last_login_at, last_login_ip, access_version — is absent on purpose: those are
     * written by the server from its own observations and must never be reachable from a payload.
     * Code that legitimately writes them uses forceFill().
     */
    protected $fillable = [
        'institution_id', 'person_id', 'email', 'username', 'password',
        'is_active', 'must_change_password', 'password_changed_at',
        'mfa_enabled', 'mfa_secret', 'mfa_recovery_codes',
        'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token', 'mfa_secret', 'mfa_recovery_codes'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'mfa_secret' => 'encrypted',
            'mfa_recovery_codes' => 'encrypted:array',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'mfa_enabled' => 'boolean',
            'password_changed_at' => 'immutable_datetime',
            'last_login_at' => 'immutable_datetime',
            'locked_until' => 'immutable_datetime',
            'email_verified_at' => 'immutable_datetime',
            'failed_login_attempts' => 'integer',
            'access_version' => 'integer',
        ];
    }

    /** @return BelongsTo<Person, $this> */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return HasMany<RoleAssignment, $this> */
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }

    /**
     * Currently-in-force assignments, with roles and permissions eager-loaded.
     *
     * @return Collection<int, RoleAssignment>
     */
    public function activeAssignments(): Collection
    {
        return $this->roleAssignments()
            ->active()
            ->with('role.permissions')
            ->get();
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /** A user may sign in only if the account is active, not locked, and not soft-deleted. */
    public function canAuthenticate(): bool
    {
        return $this->is_active && ! $this->isLocked() && $this->deleted_at === null;
    }

    /** @param Builder<$this> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * The scopes over which this user holds a given permission.
     *
     * Returns an EMPTY array when the user does not hold the permission anywhere — which callers
     * must treat as "denied everywhere", never as "unrestricted". That inversion is the classic
     * way an empty filter turns into a full table scan of somebody else's data.
     *
     * @return list<Scope>
     */
    public function scopesFor(string $permission): array
    {
        $scopes = [];

        foreach ($this->activeAssignments() as $assignment) {
            $granted = $assignment->role?->permissions->contains('name', $permission) ?? false;

            if ($granted) {
                $scopes[] = $assignment->scope();
            }
        }

        return $scopes;
    }
}
