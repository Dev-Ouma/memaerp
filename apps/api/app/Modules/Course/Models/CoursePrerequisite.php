<?php

declare(strict_types=1);

namespace App\Modules\Course\Models;

use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CoursePrerequisite extends BaseModel
{
    use Auditable;

    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'course.course_prerequisites';

    protected $fillable = [
        'institution_id',
        'course_id',
        'prerequisite_course_id',
        'requirement_type',
        'curriculum_version_id',
    ];

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /** @return BelongsTo<Course, $this> */
    public function prerequisiteCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }

    /** @return BelongsTo<CurriculumVersion, $this> */
    public function curriculumVersion(): BelongsTo
    {
        return $this->belongsTo(CurriculumVersion::class);
    }
}
