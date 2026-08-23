<?php

declare(strict_types=1);

namespace App\Modules\Portal\Services;

use App\Modules\Course\Services\TimetableService;
use App\Modules\Enrollment\Models\TermRegistration;
use App\Modules\Enrollment\Services\EnrollmentWorkflowService;
use App\Modules\Examination\Models\TermGpa;
use App\Modules\Examination\Services\ProgressionService;
use App\Modules\Finance\Services\ClearanceService;
use App\Modules\Finance\Services\FinanceService;
use App\Modules\Graduation\Services\DegreeAuditService;
use App\Modules\Institution\Models\Term;
use App\Modules\Portal\Models\StudentPreference;
use App\Modules\Student\Models\Student;

final class PortalDashboardService
{
    public function __construct(
        private readonly FinanceService $finance,
        private readonly ClearanceService $clearance,
        private readonly EnrollmentWorkflowService $enrollment,
        private readonly TimetableService $timetable,
        private readonly ProgressionService $progression,
        private readonly DegreeAuditService $degreeAudit,
    ) {}

    /** @return array<string, mixed> */
    public function dashboard(Student $student): array
    {
        $term = Term::query()->where('institution_id', $student->institution_id)->current()->first();
        $statement = $this->finance->statement($student->institution_id, $student->person_id);
        $registration = $term ? TermRegistration::query()->where('student_id', $student->id)->where('term_id', $term->id)->first() : null;
        $nextClasses = $term ? $this->timetable->mySchedule($student, $term->id)->take(3)->values() : collect();
        $latestGpa = TermGpa::query()->where('student_id', $student->id)->where('is_published', true)->orderByDesc('published_at')->first();

        return [
            'student' => [
                'student_number' => $student->student_number,
                'full_name' => $student->person?->full_name,
                'programme' => $student->programme?->name,
                'status' => $student->status,
            ],
            'finance' => $statement['clearance'],
            'registration' => [
                'term' => $term?->only(['id', 'code', 'name']),
                'registered' => $registration !== null,
                'status' => $registration?->status,
                'course_count' => $registration?->courseEnrollments()->where('status', 'ENROLLED')->count() ?? 0,
            ],
            'academics' => [
                'latest_gpa' => $latestGpa?->gpa,
                'latest_cgpa' => $latestGpa?->cgpa,
                'standing' => $latestGpa?->academic_standing ?? $student->academic_standing,
            ],
            'graduation_audit' => $this->degreeAudit->audit($student),
            'next_classes' => $nextClasses,
            'alerts' => $this->alerts($student, $term, $statement['clearance']),
        ];
    }

    /** @return list<array<string, string>> */
    public function alerts(Student $student, ?Term $term, array $clearance): array
    {
        $alerts = [];
        if (! $clearance['registration_cleared']) {
            $alerts[] = ['level' => 'warning', 'message' => 'Fee payment below registration threshold. Settle fees to register.'];
        }
        if ($term !== null && $term->registrationIsOpen() && ! TermRegistration::query()->where('student_id', $student->id)->where('term_id', $term->id)->exists()) {
            $alerts[] = ['level' => 'info', 'message' => 'Term registration is open. Complete your registration.'];
        }

        return $alerts;
    }

    /** @return list<array<string, string>> */
    public function documents(Student $student): array
    {
        $docs = [
            ['title' => 'Digital Student ID', 'url' => url('/api/v1/students/'.$student->id.'/digital-id'), 'type' => 'PDF'],
            ['title' => 'Exam Card', 'url' => url('/api/v1/exams/my-card'), 'type' => 'PDF'],
            ['title' => 'Official Transcript', 'url' => url('/api/v1/graduation/transcript'), 'type' => 'PDF'],
        ];

        $term = Term::query()->where('institution_id', $student->institution_id)->current()->first();
        $registration = $term ? TermRegistration::query()->where('student_id', $student->id)->where('term_id', $term->id)->first() : null;
        if ($registration !== null) {
            $docs[] = ['title' => 'Registration Slip', 'url' => url('/api/v1/enrollment/registrations/'.$registration->id.'/slip'), 'type' => 'PDF'];
        }

        return $docs;
    }

    public function preferences(string $institutionId, string $personId): StudentPreference
    {
        return StudentPreference::query()->firstOrCreate(
            ['person_id' => $personId],
            [
                'institution_id' => $institutionId,
                'locale' => 'en-KE',
                'theme' => 'light',
                'email_alerts' => true,
                'sms_alerts' => true,
            ],
        );
    }
}
