<?php

declare(strict_types=1);

namespace App\Modules\Course\Models;

use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CoursePrerequisite extends BaseModel
{
    use HasFactory;

    protected $table = 'course.course_prerequisites';

    protected $fillable = [
        'course_id',
        'prerequisite_course_id',
        'requirement_type',
    ];

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
}
