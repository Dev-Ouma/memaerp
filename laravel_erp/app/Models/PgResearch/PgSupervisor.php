<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'staff_no', 'full_name', 'academic_rank', 'department', 'specialization', 'max_load', 'is_active'])]
final class PgSupervisor extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'max_load' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PgSupervisorAllocation::class, 'supervisor_id');
    }

    public function activeLoad(): int
    {
        return $this->allocations()->where('status', 'ACTIVE')->count();
    }
}
