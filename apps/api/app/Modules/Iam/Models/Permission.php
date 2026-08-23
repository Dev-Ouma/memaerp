<?php

declare(strict_types=1);

namespace App\Modules\Iam\Models;

use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * An atomic capability, named `module.resource.action` — e.g. `examination.marks.approve`.
 *
 * Permissions are NOT institution-scoped: the catalogue is a property of the software, seeded
 * from code, identical everywhere. What varies per institution is which roles hold them.
 */
final class Permission extends BaseModel
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'iam.permissions';

    protected $fillable = ['name', 'module', 'resource', 'action', 'description', 'is_sensitive'];

    protected function casts(): array
    {
        return ['is_sensitive' => 'boolean'];
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'iam.permission_role');
    }
}
