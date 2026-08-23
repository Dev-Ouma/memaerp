<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Models;

use App\Modules\Course\Models\Course;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CurriculumCourse extends BaseModel
{
    use HasFactory;

    protected $table = 'curriculum.curriculum_courses';

    protected $fillable = [
        'curriculum_version_id',
        'course_id',
        'year_level',
        'semester',
        'course_type',
        'elective_group',
    ];

    protected function casts(): array
    {
        return [
            'year_level' => 'integer',
            'semester' => 'integer',
        ];
    }

    /** @return BelongsTo<CurriculumVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(CurriculumVersion::class, 'curriculum_version_id');
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
