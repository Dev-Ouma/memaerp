<?php

declare(strict_types=1);

namespace App\Modules\Institution\Models;

use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AcademicYear extends BaseModel
{
    use HasFactory;

    protected $table = 'institution.academic_years';

    protected $fillable = ['institution_id', 'code', 'name', 'starts_on', 'ends_on', 'is_current'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return HasMany<Term, $this> */
    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    /** @param Builder<$this> $query */
    public function scopeCurrent(Builder $query): void
    {
        $query->where('is_current', true);
    }
}
