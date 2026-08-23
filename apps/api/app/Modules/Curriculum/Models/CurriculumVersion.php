<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Models;

use App\Modules\Course\Models\CoursePrerequisite;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CurriculumVersion extends BaseModel
{
    use Auditable;

    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'curriculum.curriculum_versions';

    protected $fillable = [
        'institution_id',
        'programme_id',
        'effective_year_id',
        'version_code',
        'senate_approval_ref',
        'is_approved',
        'approved_at',
        'status',
        'graduation_credits_required',
        'minimum_elective_credits',
        'submitted_at',
        'locked_at',
        'structure_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'approved_at' => 'immutable_datetime',
            'graduation_credits_required' => 'integer',
            'minimum_elective_credits' => 'integer',
            'submitted_at' => 'immutable_datetime',
            'locked_at' => 'immutable_datetime',
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
    public function effectiveYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'effective_year_id');
    }

    /** @return HasMany<CurriculumCourse, $this> */
    public function curriculumCourses(): HasMany
    {
        return $this->hasMany(CurriculumCourse::class);
    }

    /** @return HasMany<ElectiveGroup, $this> */
    public function electiveGroups(): HasMany
    {
        return $this->hasMany(ElectiveGroup::class);
    }

    /** @return HasMany<ReviewStep, $this> */
    public function reviewSteps(): HasMany
    {
        return $this->hasMany(ReviewStep::class)->orderBy('sequence');
    }

    /** @return HasMany<CoursePrerequisite, $this> */
    public function requirements(): HasMany
    {
        return $this->hasMany(CoursePrerequisite::class);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['APPROVED', 'SUPERSEDED'], true);
    }
}
