<?php

declare(strict_types=1);

namespace App\Modules\Iam\Models;

use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A named bundle of permissions. Roles carry no scope of their own — "Head of Department" is a
 * role; WHICH department is decided by the {@see RoleAssignment} that grants it.
 *
 * System roles (`is_system`) are seeded and may not be edited or deleted through the UI. Without
 * that, an administrator can quietly strip permissions from the role that oversees them.
 */
final class Role extends BaseModel
{
    use Auditable;
    use HasFactory;

    public const string FAMILY_STUDENT = 'student';

    public const string FAMILY_ACADEMIC = 'academic';

    public const string FAMILY_ADMINISTRATIVE = 'administrative';

    public const string FAMILY_EXECUTIVE = 'executive';

    public const string FAMILY_SYSTEM = 'system';

    protected $table = 'iam.roles';

    protected $fillable = ['institution_id', 'code', 'name', 'description', 'family', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsToMany<Permission, $this> */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'iam.permission_role');
    }

    /** @return HasMany<RoleAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }
}
