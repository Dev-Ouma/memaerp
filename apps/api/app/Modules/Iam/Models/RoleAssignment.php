<?php

declare(strict_types=1);

namespace App\Modules\Iam\Models;

use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use App\Platform\Support\Scope;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The grant that ties the three authorization dimensions together: this USER holds this ROLE
 * over this SCOPE, optionally only between two dates.
 *
 * Time-bounding matters more than it looks. Acting appointments, exam-season marks-entry rights
 * and vendor support access all need to expire on their own; relying on someone remembering to
 * revoke them is how dormant privilege accumulates. `granted_by` and `grant_reason` exist so
 * that every elevation can be answered for later.
 */
final class RoleAssignment extends BaseModel
{
    use Auditable;

    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'iam.role_assignments';

    protected $fillable = [
        'institution_id', 'user_id', 'role_id',
        'scope_type', 'scope_id',
        'starts_at', 'ends_at', 'granted_by', 'grant_reason',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** @return BelongsTo<User, $this> */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function scope(): Scope
    {
        return new Scope($this->scope_type, $this->scope_id);
    }

    public function isActiveAt(?CarbonImmutable $at = null): bool
    {
        $at ??= CarbonImmutable::now();

        if ($this->starts_at !== null && $at->lessThan($this->starts_at)) {
            return false;
        }

        return ! ($this->ends_at !== null && $at->greaterThan($this->ends_at));
    }

    /**
     * Only assignments in force right now. Every authorization read goes through this — an
     * expired grant must be invisible, not merely flagged.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query, ?CarbonImmutable $at = null): void
    {
        $at ??= CarbonImmutable::now();

        $query->where(function (Builder $q) use ($at): void {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at);
        })->where(function (Builder $q) use ($at): void {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', $at);
        });
    }
}
