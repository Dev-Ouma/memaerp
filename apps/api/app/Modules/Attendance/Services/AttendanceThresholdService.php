<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\AtRiskAlert;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Attendance\Models\SessionLog;
use App\Modules\Enrollment\Models\CourseEnrollment;
use Illuminate\Support\Carbon;

final class AttendanceThresholdService
{
    public function evaluateOffering(string $offeringId): void
    {
        $studentIds = CourseEnrollment::query()
            ->where('course_offering_id', $offeringId)
            ->where('status', 'ENROLLED')
            ->pluck('student_id');

        foreach ($studentIds as $studentId) {
            $this->evaluateStudent((string) $studentId, $offeringId);
        }
    }

    public function evaluateStudent(string $studentId, string $offeringId): void
    {
        $percentage = $this->percentageFor($studentId, $offeringId);
        $threshold = (float) config('attendance.threshold_percentage', 75);

        if ($percentage >= $threshold) {
            AtRiskAlert::query()
                ->where('student_id', $studentId)
                ->where('course_offering_id', $offeringId)
                ->where('status', 'OPEN')
                ->update(['status' => 'RESOLVED', 'resolved_at' => Carbon::now()]);

            return;
        }

        $session = AttendanceSession::query()->where('course_offering_id', $offeringId)->first();
        if ($session === null) {
            return;
        }

        AtRiskAlert::query()->updateOrCreate(
            [
                'student_id' => $studentId,
                'course_offering_id' => $offeringId,
                'status' => 'OPEN',
            ],
            [
                'institution_id' => $session->institution_id,
                'attendance_percentage' => $percentage,
                'threshold_percentage' => $threshold,
                'flagged_at' => Carbon::now(),
            ],
        );
    }

    public function percentageFor(string $studentId, string $offeringId): float
    {
        $totalSessions = AttendanceSession::query()
            ->where('course_offering_id', $offeringId)
            ->where('status', 'CLOSED')
            ->count();

        if ($totalSessions === 0) {
            return 100.0;
        }

        $attended = SessionLog::query()
            ->where('course_offering_id', $offeringId)
            ->where('student_id', $studentId)
            ->whereIn('status', ['PRESENT', 'LATE'])
            ->distinct('session_date')
            ->count('session_date');

        return round(($attended / $totalSessions) * 100, 2);
    }
}
