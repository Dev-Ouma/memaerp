<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Models;

use App\Modules\Course\Models\CourseOffering;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AtRiskAlert extends BaseModel
{
    protected $table = 'attendance.at_risk_alerts';

    protected $fillable = [
        'institution_id', 'student_id', 'course_offering_id',
        'attendance_percentage', 'threshold_percentage', 'status', 'flagged_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'attendance_percentage' => 'decimal:2',
            'threshold_percentage' => 'decimal:2',
            'flagged_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<CourseOffering, $this> */
    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }
}
