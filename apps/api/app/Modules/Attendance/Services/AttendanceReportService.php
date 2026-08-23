<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\AtRiskAlert;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Attendance\Models\SessionLog;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Student\Models\Student;
use Illuminate\Support\Collection;

final class AttendanceReportService
{
    public function __construct(private readonly AttendanceThresholdService $thresholds) {}

    /** @return array<string, mixed> */
    public function studentRecord(Student $student): array
    {
        $enrollments = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'ENROLLED')
            ->with(['courseOffering.course'])
            ->get();

        $courses = $enrollments->map(function (CourseEnrollment $enrollment) use ($student): array {
            $offeringId = (string) $enrollment->course_offering_id;
            $percentage = $this->thresholds->percentageFor($student->id, $offeringId);
            $logs = SessionLog::query()
                ->where('student_id', $student->id)
                ->where('course_offering_id', $offeringId)
                ->orderByDesc('session_date')
                ->limit(20)
                ->get();

            return [
                'offering_id' => $offeringId,
                'course_code' => $enrollment->courseOffering?->course?->code,
                'course_title' => $enrollment->courseOffering?->course?->title,
                'section_code' => $enrollment->courseOffering?->section_code,
                'attendance_percentage' => $percentage,
                'threshold' => (float) config('attendance.threshold_percentage', 75),
                'at_risk' => $percentage < (float) config('attendance.threshold_percentage', 75),
                'recent_logs' => $logs,
            ];
        });

        return [
            'student_id' => $student->id,
            'courses' => $courses,
        ];
    }

    /** @return array<string, mixed> */
    public function courseReport(CourseOffering $offering): array
    {
        $offering->loadMissing(['course', 'term']);
        $sessions = AttendanceSession::query()
            ->where('course_offering_id', $offering->id)
            ->orderByDesc('session_date')
            ->limit(30)
            ->get();

        $enrollments = CourseEnrollment::query()
            ->where('course_offering_id', $offering->id)
            ->where('status', 'ENROLLED')
            ->with(['student.person'])
            ->get();

        $students = $enrollments->map(function (CourseEnrollment $enrollment) use ($offering): array {
            $studentId = (string) $enrollment->student_id;
            $percentage = $this->thresholds->percentageFor($studentId, $offering->id);
            $present = SessionLog::query()
                ->where('course_offering_id', $offering->id)
                ->where('student_id', $studentId)
                ->count();

            return [
                'student_id' => $studentId,
                'student_number' => $enrollment->student?->student_number,
                'name' => trim(($enrollment->student?->person?->first_name ?? '').' '.($enrollment->student?->person?->last_name ?? '')),
                'attendance_percentage' => $percentage,
                'sessions_attended' => $present,
                'at_risk' => $percentage < (float) config('attendance.threshold_percentage', 75),
            ];
        });

        return [
            'offering' => [
                'id' => $offering->id,
                'course_code' => $offering->course?->code,
                'course_title' => $offering->course?->title,
                'section_code' => $offering->section_code,
                'term' => $offering->term?->name,
            ],
            'sessions' => $sessions,
            'students' => $students,
            'total_sessions' => $sessions->where('status', 'CLOSED')->count(),
        ];
    }

    /** @return Collection<int, AtRiskAlert> */
    public function atRiskAlerts(string $institutionId): Collection
    {
        return AtRiskAlert::query()
            ->where('institution_id', $institutionId)
            ->where('status', 'OPEN')
            ->with(['student.person', 'courseOffering.course'])
            ->orderBy('attendance_percentage')
            ->get();
    }
}
