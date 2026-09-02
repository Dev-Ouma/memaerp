<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mapping_ref', 'task_management_role_id', 'task_template', 'trigger_event', 'sla_hours', 'is_active'])]
final class TaskRoleBinding extends Model
{
    public function role(): BelongsTo
    {
        return $this->belongsTo(TaskManagementRole::class, 'task_management_role_id');
    }
}
