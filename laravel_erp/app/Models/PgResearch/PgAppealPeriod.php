<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'academic_year', 'term_label', 'opens_on', 'closes_on', 'status', 'notes'])]
final class PgAppealPeriod extends Model
{
    protected function casts(): array
    {
        return ['opens_on' => 'date', 'closes_on' => 'date'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PgAppealCategory::class, 'category_id');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(PgAppeal::class, 'period_id');
    }

    public function isAcceptingSubmissions(): bool
    {
        return $this->status === 'OPEN'
            && $this->opens_on->startOfDay()->lte(now())
            && $this->closes_on->endOfDay()->gte(now());
    }
}
