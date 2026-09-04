<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class StudentRegistrationService
{
    /** Ceiling on the search for a free serial, so a bad index cannot hang enrolment. */
    private const MAX_SERIAL_PROBES = 5000;

    public function __construct(private readonly AcademicYearService $academicYears) {}

    /**
     * Register a brand-new student, provisioning the login account as well.
     * Used by staff creating a student directly, outside the admission funnel.
     */
    public function register(array $data): Student
    {
        return DB::transaction(function () use ($data): Student {
            $course = Course::query()->lockForUpdate()->findOrFail($data['course_id']);
            $registrationNumber = $this->nextRegistrationNumber($course);

            $user = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => strtolower(str_replace('/', '', $registrationNumber)).'@student.mema.ac.ke',
                'role' => 'student',
                'password' => 'password',
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => true,
            ]);

            return $this->createStudentRecord($user, $course, $registrationNumber);
        });
    }

    /**
     * Enrol an account that already exists. This is the path an admitted
     * applicant takes at conversion: the login, email address and audit trail
     * built up during admission carry over to the student record rather than a
     * second identity being created for the same person.
     *
     * Idempotent — a user who already has a student record gets it back
     * unchanged, and no registration serial is burned.
     */
    public function enrolExistingUser(User $user, Course $course): Student
    {
        return DB::transaction(function () use ($user, $course): Student {
            $existing = Student::query()->where('user_id', $user->id)->first();
            if ($existing !== null) {
                return $existing;
            }

            $course = Course::query()->lockForUpdate()->findOrFail($course->id);
            $student = $this->createStudentRecord($user, $course, $this->nextRegistrationNumber($course));
            $this->promoteToStudent($user);

            return $student;
        });
    }

    /**
     * Allocate the next registration number for a course. The caller must hold
     * the course row lock: the serial is read and incremented non-atomically.
     *
     * The counter alone is not trustworthy. Students imported, seeded or created
     * before the counter existed hold numbers it has never issued, so allocating
     * straight from it collides on the unique index and takes down the enrolment
     * that triggered it. The serial is therefore advanced past every number the
     * course has actually issued before one is handed out.
     */
    private function nextRegistrationNumber(Course $course): string
    {
        $token = strtoupper(trim((string) $course->code));
        if ($token === '') {
            $token = 'CRS'.str_pad((string) $course->id, 3, '0', STR_PAD_LEFT);
        }

        $year = now()->format('Y');
        $serial = max((int) $course->next_student_serial, 1);

        // Bounded so a corrupt index can never spin here forever; the ceiling is
        // far above any plausible cohort size for a single course and year.
        for ($attempt = 0; $attempt < self::MAX_SERIAL_PROBES; $attempt++) {
            $candidate = sprintf('%s/%03d/%s', $token, $serial, $year);
            if (! Student::query()->where('admission_number', $candidate)->exists()) {
                $course->forceFill(['next_student_serial' => $serial + 1])->save();

                return $candidate;
            }
            $serial++;
        }

        throw new RuntimeException(
            "Could not allocate a free registration number for {$token} in {$year} after ".self::MAX_SERIAL_PROBES.' attempts.',
        );
    }

    private function createStudentRecord(User $user, Course $course, string $registrationNumber): Student
    {
        return Student::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'academic_session_id' => $this->academicYears->current()->id,
            'admission_number' => $registrationNumber,
        ]);
    }

    private function promoteToStudent(User $user): void
    {
        if ($user->role !== 'student') {
            $user->forceFill(['role' => 'student'])->save();
        }

        // The User::created() hook only seeds a stakeholder type for new
        // accounts, so a promoted applicant needs the student type added here.
        if (Schema::hasTable('user_stakeholder_types')) {
            $user->stakeholderTypes()->firstOrCreate(['stakeholder_type' => 'student'], ['is_active' => true]);
        }
    }
}
