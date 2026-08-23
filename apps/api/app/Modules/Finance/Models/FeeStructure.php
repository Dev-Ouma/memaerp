<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FeeStructure extends BaseModel
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'finance.fee_structures';

    protected $fillable = [
        'institution_id',
        'programme_id',
        'academic_year_id',
        'name',
        'year_level',
        'semester',
        'tuition_fee',
        'statutory_fees',
        'total_amount',
        'currency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'year_level' => 'integer',
            'semester' => 'integer',
            'tuition_fee' => 'decimal:2',
            'statutory_fees' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Programme, $this> */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
