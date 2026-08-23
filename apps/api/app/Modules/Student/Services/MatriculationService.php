<?php

declare(strict_types=1);

namespace App\Modules\Student\Services;

use App\Modules\Admission\Models\Application;
use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\RoleAssignment;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Department;
use App\Modules\Student\Models\MatriculationLog;
use App\Modules\Student\Models\PersonIdentity;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Notifications\StudentNumberIssuedNotification;
use App\Platform\Support\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

final class MatriculationService
{
    public function __construct(
        private readonly StudentNumberService $numbers,
        private readonly DigitalIdService $digitalId,
    ) {}

    /**
     * @param  list<string>  $applicationIds
     * @return list<Student>
     */
    public function matriculateBatch(User $actor, array $applicationIds, bool $pledgeSigned = false, ?string $notes = null): array
    {
        $students = [];

        foreach ($applicationIds as $applicationId) {
            $students[] = $this->matriculate($actor, $applicationId, $pledgeSigned, $notes);
        }

        return $students;
    }

    public function matriculate(User $actor, string $applicationId, bool $pledgeSigned = false, ?string $notes = null): Student
    {
        $application = Application::query()
            ->with(['person', 'programme.department.faculty', 'campus', 'academicYear', 'intake', 'studyMode'])
            ->findOrFail($applicationId);

        if ($application->status !== 'ACCEPTED') {
            throw ValidationException::withMessages([
                'application_id' => ['Only accepted applications can be matriculated. Current status: '.$application->status],
            ]);
        }

        if ($application->documents_verified_at === null) {
            throw ValidationException::withMessages([
                'documents' => ['Original documents must be verified before matriculation.'],
            ]);
        }

        if (Student::query()->where('application_id', $application->id)->exists()) {
            throw ValidationException::withMessages([
                'application_id' => ['This application has already been matriculated.'],
            ]);
        }

        if (Student::query()->where('person_id', $application->person_id)->where('status', 'ACTIVE')->exists()) {
            throw ValidationException::withMessages([
                'person_id' => ['This person already has an active student record.'],
            ]);
        }

        $person = $application->person;
        if ($person?->date_of_birth !== null && $person->date_of_birth->age < 15) {
            throw ValidationException::withMessages([
                'date_of_birth' => ['Student must be at least 15 years old at matriculation.'],
            ]);
        }

        $programme = $application->programme;
        $admissionYear = $application->academicYear;
        abort_unless($programme !== null && $admissionYear !== null, 422, 'Application is missing programme or admission year.');

        $curriculumVersion = CurriculumVersion::query()
            ->where('programme_id', $programme->id)
            ->where('status', 'APPROVED')
            ->orderByDesc('approved_at')
            ->first();

        if ($curriculumVersion === null) {
            throw ValidationException::withMessages([
                'curriculum' => ['No approved curriculum version exists for this programme.'],
            ]);
        }

        /** @var Department $department */
        $department = $programme->department;

        return DB::transaction(function () use (
            $actor,
            $application,
            $programme,
            $admissionYear,
            $curriculumVersion,
            $department,
            $pledgeSigned,
            $notes,
        ): Student {
            $studentNumber = $this->numbers->allocate(
                $application->institution_id,
                $programme,
                $admissionYear,
            );

            $student = Student::query()->create([
                'institution_id' => $application->institution_id,
                'person_id' => $application->person_id,
                'application_id' => $application->id,
                'programme_id' => $programme->id,
                'curriculum_version_id' => $curriculumVersion->id,
                'campus_id' => $application->campus_id,
                'department_id' => $department->id,
                'faculty_id' => $department->faculty_id,
                'admission_year_id' => $admissionYear->id,
                'intake_id' => $application->intake_id,
                'study_mode_id' => $application->study_mode_id,
                'student_number' => $studentNumber,
                'current_year_level' => 1,
                'current_semester' => 1,
                'academic_standing' => 'GOOD_STANDING',
                'status' => 'ACTIVE',
                'matriculated_on' => now()->toDateString(),
                'digital_id_status' => 'INACTIVE',
            ]);

            $token = $this->digitalId->issueToken($student);
            $student->forceFill([
                'digital_id_token' => $token,
                'digital_id_issued_at' => now(),
                'digital_id_status' => 'ACTIVE',
            ])->save();

            PersonIdentity::query()->create([
                'institution_id' => $application->institution_id,
                'person_id' => $application->person_id,
                'identity_type' => PersonIdentity::TYPE_STUDENT,
                'identifier' => $studentNumber,
                'status' => PersonIdentity::STATUS_ACTIVE,
                'started_on' => now()->toDateString(),
                'metadata' => ['application_id' => $application->id],
            ]);

            MatriculationLog::query()->create([
                'institution_id' => $application->institution_id,
                'application_id' => $application->id,
                'student_id' => $student->id,
                'matriculated_by' => $actor->id,
                'matriculated_at' => now(),
                'original_documents_verified' => true,
                'pledge_signed' => $pledgeSigned,
                'notes' => $notes,
            ]);

            $application->auditReason('Applicant matriculated into student registry')->forceFill([
                'status' => 'MATRICULATED',
            ])->save();

            $this->promoteApplicantToStudent($application->person_id, $application->institution_id);

            $student = $student->fresh([
                'person',
                'programme.department',
                'campus',
                'admissionYear',
                'intake',
                'studyMode',
                'curriculumVersion',
            ]) ?? $student;

            $email = $student->person?->primary_email;
            if (is_string($email) && $email !== '') {
                Notification::route('mail', $email)
                    ->notify(new StudentNumberIssuedNotification($student));
            }

            return $student;
        });
    }

    private function promoteApplicantToStudent(string $personId, string $institutionId): void
    {
        $user = User::query()->where('person_id', $personId)->where('institution_id', $institutionId)->first();
        if ($user === null) {
            return;
        }

        $studentRole = Role::query()->where('institution_id', $institutionId)->where('code', 'student')->first();
        if ($studentRole === null) {
            return;
        }

        $hasStudentRole = RoleAssignment::query()
            ->where('user_id', $user->id)
            ->where('role_id', $studentRole->id)
            ->whereNull('ends_at')
            ->exists();

        if (! $hasStudentRole) {
            RoleAssignment::query()->create([
                'institution_id' => $institutionId,
                'user_id' => $user->id,
                'role_id' => $studentRole->id,
                'scope_type' => Scope::SELF,
                'scope_id' => null,
                'grant_reason' => 'Matriculation from accepted application',
                'starts_at' => now(),
            ]);
        }

        $applicantRole = Role::query()->where('institution_id', $institutionId)->where('code', 'applicant')->first();
        if ($applicantRole !== null) {
            RoleAssignment::query()
                ->where('user_id', $user->id)
                ->where('role_id', $applicantRole->id)
                ->whereNull('ends_at')
                ->update(['ends_at' => now()]);
        }
    }
}
