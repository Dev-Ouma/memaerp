<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'department', 'is_active', 'granted_by', 'granted_at'])]
final class BudgetSubmitter extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'granted_at' => 'immutable_datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
