<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description', 'applies_to', 'fee_amount', 'sla_days', 'requires_evidence', 'is_active'])]
final class PgAppealCategory extends Model
{
    protected function casts(): array
    {
        return [
            'fee_amount' => 'decimal:2',
            'sla_days' => 'integer',
            'requires_evidence' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function periods(): HasMany
    {
        return $this->hasMany(PgAppealPeriod::class, 'category_id');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(PgAppeal::class, 'category_id');
    }
}
