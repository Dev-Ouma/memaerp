<?php

declare(strict_types=1);

namespace App\Modules\Course\Services;

use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CourseReview;
use App\Modules\Iam\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CourseWorkflowService
{
    /** @var array<string, int> */
    public const STAGES = ['DEPARTMENT_BOARD' => 1, 'SCHOOL_BOARD' => 2];

    public function submit(Course $course): Course
    {
        if ($course->status !== 'DRAFT') {
            throw ValidationException::withMessages(['status' => ['Only a draft course can be submitted for departmental review.']]);
        }

        return DB::transaction(function () use ($course): Course {
            foreach (self::STAGES as $stage => $sequence) {
                CourseReview::query()->firstOrCreate([
                    'course_id' => $course->id,
                    'stage' => $stage,
                ], [
                    'institution_id' => $course->institution_id,
                    'sequence' => $sequence,
                    'status' => 'PENDING',
                ]);
            }
            $course->auditReason('Course submitted for department and school board review')
                ->forceFill(['status' => 'UNDER_REVIEW', 'is_active' => false])
                ->save();

            return $course->fresh($this->relations()) ?? $course;
        });
    }

    public function approveNext(Course $course, User $actor, string $stage, string $reference, ?string $comments): Course
    {
        if ($course->status !== 'UNDER_REVIEW') {
            throw ValidationException::withMessages(['status' => ['The course must be under review.']]);
        }

        return DB::transaction(function () use ($course, $actor, $stage, $reference, $comments): Course {
            $next = CourseReview::query()->where('course_id', $course->id)
                ->where('status', 'PENDING')->orderBy('sequence')->lockForUpdate()->firstOrFail();
            if ($next->stage !== $stage) {
                throw ValidationException::withMessages(['stage' => ["{$next->stage} approval is required next."]]);
            }
            $next->auditReason("{$stage} course review completed")->update([
                'status' => 'APPROVED',
                'reviewed_by' => $actor->id,
                'reference' => $reference,
                'comments' => $comments,
                'reviewed_at' => now(),
            ]);

            $updates = $stage === 'DEPARTMENT_BOARD'
                ? ['department_board_ref' => $reference]
                : [
                    'school_board_ref' => $reference,
                    'status' => 'ACTIVE',
                    'is_active' => true,
                    'approved_at' => now(),
                ];
            $course->auditReason($stage === 'SCHOOL_BOARD' ? 'Course approved by school board' : 'Department board approved the course')
                ->forceFill($updates)
                ->save();

            return $course->fresh($this->relations()) ?? $course;
        });
    }

    /** @return list<string> */
    private function relations(): array
    {
        return ['department.faculty', 'prerequisites.prerequisiteCourse', 'reviews.reviewer'];
    }
}
