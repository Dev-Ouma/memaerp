<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admission\StudentConversion;
use App\Models\AdmissionApplication;
use App\Models\AuditLog;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Turns an admitted applicant into a student.
 *
 * The conversion is the seam between the admission funnel and the academic
 * records of the ERP, so it is recorded explicitly in `student_conversions`
 * rather than being inferred from the application status: one row per
 * application, carrying the outcome, the resulting student number and — when
 * something goes wrong — the reason it failed.
 */
final class StudentConversionService
{
    public function __construct(private readonly StudentRegistrationService $registrations) {}

    /**
     * Convert an application, at most once.
     *
     * Safe to call repeatedly: a completed conversion is returned untouched, a
     * failed one is retried, and no duplicate student is created.
     */
    public function convert(AdmissionApplication $application, ?int $actorId = null): StudentConversion
    {
        $conversion = $this->claim($application, $actorId);
        if ($conversion->status === 'COMPLETED') {
            return $conversion;
        }

        try {
            return DB::transaction(fn (): StudentConversion => $this->complete($conversion, $application, $actorId));
        } catch (Throwable $exception) {
            // Written outside the rolled-back transaction so the diagnosis survives.
            $conversion->forceFill([
                'status' => 'FAILED',
                'failure_code' => class_basename($exception),
                'failure_reason' => Str::limit($exception->getMessage(), 490),
            ])->save();
            AuditLog::record('admission.student_conversion_failed', $conversion, null, [
                'failure_code' => $conversion->failure_code,
                'failure_reason' => $conversion->failure_reason,
            ]);

            throw $exception;
        }
    }

    /**
     * Reserve the conversion row up front so a concurrent second call finds it
     * and blocks, instead of both sides racing to create a student.
     */
    private function claim(AdmissionApplication $application, ?int $actorId): StudentConversion
    {
        return DB::transaction(function () use ($application, $actorId): StudentConversion {
            $conversion = StudentConversion::query()
                ->where('admission_application_id', $application->id)
                ->lockForUpdate()
                ->first();

            if ($conversion !== null) {
                return $conversion;
            }

            return StudentConversion::create([
                'admission_application_id' => $application->id,
                'idempotency_key' => 'student-conversion:'.$application->id,
                'status' => 'PENDING',
                'converted_by' => $actorId,
                'correlation_id' => (string) Str::uuid(),
                'payload' => [],
            ]);
        });
    }

    private function complete(StudentConversion $conversion, AdmissionApplication $application, ?int $actorId): StudentConversion
    {
        $application = AdmissionApplication::query()
            ->with(['applicant.user', 'offering.course', 'offer'])
            ->lockForUpdate()
            ->findOrFail($application->id);

        $user = $application->applicant?->user;
        if ($user === null) {
            throw new RuntimeException('The application has no applicant account to convert.');
        }

        $course = $application->offering?->course;
        if ($course === null) {
            throw new RuntimeException('The programme offering has no course attached, so no student record can be created.');
        }

        $student = $this->registrations->enrolExistingUser($user, $course);

        $conversion->forceFill([
            'student_id' => $student->id,
            'student_number' => $student->admission_number,
            'status' => 'COMPLETED',
            'converted_by' => $actorId ?? $conversion->converted_by,
            'converted_at' => now(),
            'failure_code' => null,
            'failure_reason' => null,
            'payload' => [
                'application_number' => $application->application_number,
                'applicant_number' => $application->applicant->applicant_number,
                'programme' => $course->name,
                'programme_offering_id' => $application->programme_offering_id,
                'offer_number' => $application->offer?->offer_number,
                'offer_responded_at' => $application->offer?->responded_at?->toIso8601String(),
            ],
        ])->save();

        AuditLog::record('admission.student_conversion_completed', $conversion, null, [
            'student_id' => $student->id,
            'student_number' => $student->admission_number,
            'application_number' => $application->application_number,
        ]);

        return $conversion;
    }

    /**
     * The student produced by a completed conversion, if there is one.
     */
    public function studentFor(AdmissionApplication $application): ?Student
    {
        $conversion = StudentConversion::query()
            ->where('admission_application_id', $application->id)
            ->where('status', 'COMPLETED')
            ->first();

        return $conversion?->student_id === null ? null : Student::find($conversion->student_id);
    }
}
