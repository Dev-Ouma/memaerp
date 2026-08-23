<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\AtRiskAlert;
use App\Modules\Attendance\Models\SessionLog;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class AttendanceCheckInService
{
    public function __construct(private readonly AttendanceSessionService $sessions) {}

    public function checkIn(User $user, string $plainToken): SessionLog
    {
        $session = $this->sessions->findByToken($plainToken);
        if ($session === null) {
            throw ValidationException::withMessages([
                'token' => ['This QR code is invalid or has expired.'],
            ]);
        }

        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();
        $enrolled = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('course_offering_id', $session->course_offering_id)
            ->where('status', 'ENROLLED')
            ->exists();

        if (! $enrolled) {
            throw ValidationException::withMessages([
                'token' => ['You are not enrolled in this class.'],
            ]);
        }

        $existing = SessionLog::query()
            ->where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'token' => ['You have already checked in for this session.'],
            ]);
        }

        $grace = (int) config('attendance.late_grace_minutes', 10);
        $status = Carbon::now()->diffInMinutes($session->opened_at) > $grace ? 'LATE' : 'PRESENT';

        $log = SessionLog::query()->create([
            'institution_id' => $session->institution_id,
            'session_id' => $session->id,
            'course_offering_id' => $session->course_offering_id,
            'student_id' => $student->id,
            'session_date' => $session->session_date,
            'check_in_time' => Carbon::now(),
            'status' => $status,
            'method' => 'QR',
        ]);

        app(AttendanceThresholdService::class)->evaluateStudent($student->id, $session->course_offering_id);

        return $log->load(['courseOffering.course', 'student.person']);
    }
}
