<?php

declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Modules\Curriculum\Models\Programme;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StudentNumberSequence extends BaseModel
{
    protected $table = 'student.number_sequences';

    protected $fillable = [
        'institution_id',
        'programme_id',
        'academic_year_id',
        'last_sequence',
    ];

    protected function casts(): array
    {
        return [
            'last_sequence' => 'integer',
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
