<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Models;

use App\Modules\Course\Models\Course;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CurriculumCourse extends BaseModel
{
    use Auditable;

    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'curriculum.curriculum_courses';

    protected $fillable = [
        'institution_id',
        'curriculum_version_id',
        'course_id',
        'year_level',
        'semester',
        'course_type',
        'elective_group',
        'elective_group_id',
    ];

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

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

    /** @return BelongsTo<ElectiveGroup, $this> */
    public function electiveGroup(): BelongsTo
    {
        return $this->belongsTo(ElectiveGroup::class);
    }
}
