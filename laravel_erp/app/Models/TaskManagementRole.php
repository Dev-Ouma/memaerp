<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['role_code', 'name', 'department', 'privilege_level', 'is_active'])]
final class TaskManagementRole extends Model
{
    public function bindings(): HasMany
    {
        return $this->hasMany(TaskRoleBinding::class);
    }
}
