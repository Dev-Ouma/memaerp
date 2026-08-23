<?php

declare(strict_types=1);

namespace App\Modules\Admission\Models;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProgrammeCutoff extends BaseModel
{
    use Auditable;

    protected $table = 'admission.programme_cutoffs';

    protected $fillable = [
        'institution_id', 'programme_id', 'academic_year_id',
        'minimum_score', 'minimum_mean_grade', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'minimum_score' => 'decimal:2',
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
}
