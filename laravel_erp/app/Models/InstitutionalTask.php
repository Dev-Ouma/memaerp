<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['task_ref', 'title', 'description', 'assignee_user_id', 'created_by', 'priority', 'status', 'due_at', 'lock_version', 'completed_at'])]
final class InstitutionalTask extends Model
{
    protected function casts(): array
    {
        return ['due_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime'];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }
}
