<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['institutional_task_id', 'actor_user_id', 'from_status', 'to_status', 'note', 'occurred_at'])]
final class InstitutionalTaskEvent extends Model
{
    protected function casts(): array
    {
        return ['occurred_at' => 'immutable_datetime'];
    }
}
