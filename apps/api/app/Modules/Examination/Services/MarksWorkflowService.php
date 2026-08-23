<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Examination\Models\StudentMark;
use App\Modules\Iam\Models\User;
use Illuminate\Validation\ValidationException;

final class MarksWorkflowService
{
    /** @param array<string, mixed> $scores */
    public function saveDraft(CourseEnrollment $enrollment, array $scores, User $actor): StudentMark
    {
        $this->assertMarksWindowOpen($enrollment);
        $this->assertCanEnter($actor);

        $cat = (float) ($scores['cat_score'] ?? 0);
        $exam = (float) ($scores['exam_score'] ?? 0);

        return StudentMark::query()->updateOrCreate(
            ['course_enrollment_id' => $enrollment->id],
            [
                'institution_id' => $enrollment->institution_id,
                'cat_score' => $cat,
                'exam_score' => $exam,
                'total_score' => $cat + $exam,
                'is_submitted' => false,
                'approval_status' => 'DRAFT',
            ],
        );
    }

    public function submit(CourseEnrollment $enrollment, User $actor): StudentMark
    {
        $mark = StudentMark::query()->where('course_enrollment_id', $enrollment->id)->firstOrFail();
        if ($mark->approval_status !== 'DRAFT') {
            throw ValidationException::withMessages(['status' => ['Only draft marks can be submitted.']]);
        }

        $mark->forceFill([
            'is_submitted' => true,
            'submitted_by' => $actor->id,
            'approval_status' => 'SUBMITTED',
        ])->save();

        return $mark;
    }

    public function approve(CourseEnrollment $enrollment, User $actor, string $stage): StudentMark
    {
        $mark = StudentMark::query()->where('course_enrollment_id', $enrollment->id)->firstOrFail();

        $next = match ($stage) {
            'MODERATE' => 'VERIFIED',
            'VERIFY' => 'BOARD_APPROVED',
            'BOARD' => 'SENATE_RATIFIED',
            default => throw ValidationException::withMessages(['stage' => ['Unknown approval stage.']]),
        };

        $mark->forceFill(['approval_status' => $next])->save();

        return $mark;
    }

    private function assertMarksWindowOpen(CourseEnrollment $enrollment): void
    {
        $term = $enrollment->courseOffering?->term;
        if ($term !== null && ! $term->marksEntryIsOpen()) {
            throw ValidationException::withMessages(['term' => ['Marks entry is closed for this term.']]);
        }
    }

    private function assertCanEnter(User $actor): void
    {
        if ($actor->scopesFor('examination.marks.enter') === []) {
            throw ValidationException::withMessages(['permission' => ['You cannot enter marks.']]);
        }
    }
}
