<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Models;

use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ElectiveGroup extends BaseModel
{
    use Auditable;

    protected $table = 'curriculum.elective_groups';

    protected $fillable = ['institution_id', 'curriculum_version_id', 'code', 'name', 'minimum_courses', 'minimum_credits'];

    protected function casts(): array
    {
        return ['minimum_courses' => 'integer', 'minimum_credits' => 'integer'];
    }

    /** @return BelongsTo<CurriculumVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(CurriculumVersion::class, 'curriculum_version_id');
    }

    /** @return HasMany<CurriculumCourse, $this> */
    public function curriculumCourses(): HasMany
    {
        return $this->hasMany(CurriculumCourse::class);
    }
}
