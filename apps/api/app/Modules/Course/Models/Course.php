<?php

declare(strict_types=1);

namespace App\Modules\Course\Models;

use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Course extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'course.courses';

    protected $fillable = [
        'institution_id',
        'department_id',
        'code',
        'title',
        'description',
        'credits',
        'lecture_hours',
        'lab_hours',
        'tutorial_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'lecture_hours' => 'integer',
            'lab_hours' => 'integer',
            'tutorial_hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return HasMany<CoursePrerequisite, $this> */
    public function prerequisites(): HasMany
    {
        return $this->hasMany(CoursePrerequisite::class, 'course_id');
    }

    /** @return HasMany<CourseOffering, $this> */
    public function offerings(): HasMany
    {
        return $this->hasMany(CourseOffering::class);
    }
}
