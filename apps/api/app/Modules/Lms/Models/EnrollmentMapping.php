<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Institution\Models\Institution;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EnrollmentMapping extends BaseModel
{
    protected $table = 'lms.enrollment_mappings';

    protected $fillable = [
        'institution_id', 'course_enrollment_id', 'moodle_enrollment_id', 'status', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return ['last_synced_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<CourseEnrollment, $this> */
    public function courseEnrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class);
    }
}
