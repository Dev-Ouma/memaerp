<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Models;

use App\Modules\Course\Models\CourseOffering;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SessionLog extends BaseModel
{
    protected $table = 'attendance.session_logs';

    protected $fillable = [
        'institution_id', 'session_id', 'course_offering_id', 'student_id',
        'session_date', 'check_in_time', 'status', 'method',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'check_in_time' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<AttendanceSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    /** @return BelongsTo<CourseOffering, $this> */
    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
