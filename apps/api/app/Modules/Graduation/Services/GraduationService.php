<?php

declare(strict_types=1);

namespace App\Modules\Graduation\Services;

use App\Modules\Finance\Services\ClearanceService;
use App\Modules\Graduation\Models\Certificate;
use App\Modules\Graduation\Models\GraduationApplication;
use App\Modules\Graduation\Models\GraduationClearanceCheckpoint;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class GraduationService
{
    public function __construct(
        private readonly DegreeAuditService $audit,
        private readonly ClearanceService $clearance,
    ) {}

    public function apply(Student $student): GraduationApplication
    {
        $audit = $this->audit->audit($student);
        if (! $audit['audit_passed']) {
            throw ValidationException::withMessages([
                'credits' => ['Degree audit failed. Insufficient earned credits for graduation.'],
            ]);
        }

        return $this->audit->apply($student);
    }

    public function clearCheckpoint(User $actor, GraduationClearanceCheckpoint $checkpoint, ?string $notes = null): GraduationClearanceCheckpoint
    {
        $checkpoint->load('application.student');
        $student = $checkpoint->application?->student;
        abort_unless($student instanceof Student, 422);

        if ($checkpoint->department_code === 'FIN' && ! $this->clearance->forStudent($student)['graduation_cleared']) {
            throw ValidationException::withMessages(['finance' => ['Finance clearance requires zero outstanding balance.']]);
        }

        $checkpoint->forceFill([
            'status' => 'CLEARED',
            'cleared_by' => $actor->id,
            'cleared_at' => now(),
            'notes' => $notes,
        ])->save();

        $application = $checkpoint->application;
        if ($application !== null && $application->checkpoints()->where('status', '!=', 'CLEARED')->doesntExist()) {
            $application->forceFill(['status' => 'APPROVED', 'approved_at' => now()])->save();
            $this->issueCertificate($application);
        }

        return $checkpoint->fresh() ?? $checkpoint;
    }

    public function issueCertificate(GraduationApplication $application): Certificate
    {
        $student = $application->student;
        abort_unless($student !== null, 422);

        return Certificate::query()->firstOrCreate(
            ['graduation_application_id' => $application->id],
            [
                'institution_id' => $application->institution_id,
                'student_id' => $student->id,
                'certificate_number' => 'CERT-'.now()->format('Y').'-'.strtoupper(Str::random(8)),
                'verification_token' => hash('sha256', $student->id.'|cert|'.Str::random(16)),
                'issued_at' => now(),
                'status' => 'ACTIVE',
            ],
        );
    }

    public function transcriptPdf(Student $student): Response
    {
        $student->load(['person', 'programme', 'termRegistrations.courseEnrollments.courseOffering.course', 'termRegistrations.courseEnrollments.mark']);

        return Pdf::loadView('reports.transcript', ['student' => $student])
            ->setPaper('a4')
            ->download('transcript-'.$student->student_number.'.pdf');
    }

    public function certificatePdf(Certificate $certificate): Response
    {
        $certificate->load(['student.person', 'student.programme', 'application']);

        return Pdf::loadView('reports.graduation-certificate', ['certificate' => $certificate])
            ->setPaper('a4', 'landscape')
            ->download('certificate-'.$certificate->certificate_number.'.pdf');
    }

    public function verifyCertificate(string $token): ?Certificate
    {
        return Certificate::query()
            ->with(['student.person', 'student.programme'])
            ->where('verification_token', $token)
            ->where('status', 'ACTIVE')
            ->first();
    }
}
