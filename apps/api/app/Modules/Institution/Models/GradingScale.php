<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Grading scales are effective-dated, never edited in place. When Senate changes the pass mark,
 * a NEW scale row is created with a new `effective_from`; the old one is closed with
 * `effective_to`. A 2019 transcript then still renders under 2019's rules, which is the whole
 * point — a transcript that silently re-grades itself when policy changes is a forged document.
 *
 * @property-read Collection<int, GradeBand> $bands
 */
final class GradingScale extends BaseModel
{
    use Auditable;

    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'institution.grading_scales';

    protected $fillable = ['institution_id', 'code', 'name', 'effective_from', 'effective_to'];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_to' => 'date'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return HasMany<GradeBand, $this> */
    public function bands(): HasMany
    {
        return $this->hasMany(GradeBand::class)->orderByDesc('min_mark');
    }

    /**
     * The scale in force on a given date — the only correct way to resolve a scale for a mark.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeEffectiveOn(Builder $query, CarbonInterface $date): void
    {
        $query->where('effective_from', '<=', $date)
            ->where(function (Builder $q) use ($date): void {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
            });
    }

    /** Resolve a numeric mark to its band under THIS scale. */
    public function bandFor(float $mark): ?GradeBand
    {
        return $this->bands->first(
            fn (GradeBand $band): bool => $mark >= (float) $band->min_mark && $mark <= (float) $band->max_mark,
        );
    }
}
