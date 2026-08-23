<?php

declare(strict_types=1);

namespace App\Modules\Graduation\Services;

use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Graduation\Models\GraduationApplication;
use App\Modules\Student\Models\Student;
use Illuminate\Support\Facades\DB;

final class DegreeAuditService
{
    /** @return array<string, mixed> */
    public function audit(Student $student): array
    {
        $student->loadMissing(['curriculumVersion', 'programme']);
        $required = (float) ($student->curriculumVersion?->graduation_credits_required ?? $student->programme?->total_credits_required ?? 120);

        $earned = (float) CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'ENROLLED')
            ->whereHas('mark', fn ($q) => $q->where('approval_status', 'SENATE_RATIFIED')->where('total_score', '>=', 40))
            ->with('courseOffering.course')
            ->get()
            ->sum(fn (CourseEnrollment $e) => $e->courseOffering?->course?->credits ?? 0);

        return [
            'credits_required' => $required,
            'credits_earned' => $earned,
            'credits_remaining' => max(0, $required - $earned),
            'audit_passed' => $earned >= $required,
        ];
    }

    public function apply(Student $student): GraduationApplication
    {
        $audit = $this->audit($student);

        return DB::transaction(function () use ($student, $audit): GraduationApplication {
            $application = GraduationApplication::query()->updateOrCreate(
                ['student_id' => $student->id],
                [
                    'institution_id' => $student->institution_id,
                    'programme_id' => $student->programme_id,
                    'curriculum_version_id' => $student->curriculum_version_id,
                    'status' => 'PENDING',
                    'audit_credits_required' => $audit['credits_required'],
                    'audit_credits_earned' => $audit['credits_earned'],
                    'audit_passed' => $audit['audit_passed'],
                    'applied_at' => now(),
                ],
            );

            if ($application->checkpoints()->count() === 0) {
                foreach ([
                    ['FIN', 'Finance'],
                    ['LIB', 'Library'],
                    ['REG', 'Registry'],
                ] as [$code, $name]) {
                    $application->checkpoints()->create([
                        'institution_id' => $student->institution_id,
                        'department_code' => $code,
                        'department_name' => $name,
                        'status' => 'PENDING',
                    ]);
                }
            }

            return $application->fresh(['checkpoints']) ?? $application;
        });
    }
}
