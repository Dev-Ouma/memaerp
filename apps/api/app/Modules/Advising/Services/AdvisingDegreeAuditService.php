<?php

declare(strict_types=1);

namespace App\Modules\Advising\Services;

use App\Modules\Course\Models\CoursePrerequisite;
use App\Modules\Curriculum\Models\CurriculumCourse;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Examination\Models\TermGpa;
use App\Modules\Student\Models\Student;
use Illuminate\Support\Collection;

/**
 * Real-time degree audit: transcript vs curriculum version (FR-ADV-001).
 * Uses only Senate-ratified marks per BR-MOD-02-03-002.
 */
final class AdvisingDegreeAuditService
{
    /** @return array<string, mixed> */
    public function audit(Student $student): array
    {
        $student->loadMissing(['curriculumVersion.curriculumCourses.course', 'programme', 'person']);

        $requiredCredits = (float) (
            $student->curriculumVersion?->graduation_credits_required
            ?? $student->programme?->total_credits_required
            ?? 120
        );

        $enrollments = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['ENROLLED', 'COMPLETED', 'WITHDRAWN'])
            ->with(['courseOffering.course', 'mark'])
            ->get();

        $completedCourseIds = [];
        $inProgressCourseIds = [];
        $earnedCredits = 0.0;

        foreach ($enrollments as $enrollment) {
            $courseId = $enrollment->courseOffering?->course_id;
            if ($courseId === null) {
                continue;
            }
            $mark = $enrollment->mark;
            $passed = $mark !== null
                && $mark->approval_status === 'SENATE_RATIFIED'
                && (float) $mark->total_score >= 40;

            if ($passed) {
                $completedCourseIds[$courseId] = true;
                $earnedCredits += (float) ($enrollment->courseOffering?->course?->credits ?? 0);
            } elseif ($enrollment->status === 'ENROLLED') {
                $inProgressCourseIds[$courseId] = true;
            }
        }

        $curriculumItems = CurriculumCourse::query()
            ->where('curriculum_version_id', $student->curriculum_version_id)
            ->with('course')
            ->orderBy('year_level')
            ->orderBy('semester')
            ->get();

        $completed = [];
        $inProgress = [];
        $remaining = [];

        foreach ($curriculumItems as $item) {
            $row = $this->mapRequirement($item);
            if (isset($completedCourseIds[$item->course_id])) {
                $completed[] = $row;
            } elseif (isset($inProgressCourseIds[$item->course_id])) {
                $inProgress[] = $row;
            } else {
                $remaining[] = $row;
            }
        }

        $recommendations = $this->recommend($remaining, array_keys($completedCourseIds));

        $percent = $requiredCredits > 0
            ? round(min(100, ($earnedCredits / $requiredCredits) * 100), 1)
            : 0.0;

        $latestGpa = TermGpa::query()
            ->where('student_id', $student->id)
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->first();
        $cgpa = (float) ($latestGpa?->cgpa ?? 0);

        return [
            'student_id' => $student->id,
            'student_number' => $student->student_number,
            'full_name' => $student->person?->full_name,
            'programme' => $student->programme?->name,
            'curriculum_version_id' => $student->curriculum_version_id,
            'credits_required' => $requiredCredits,
            'credits_earned' => $earnedCredits,
            'credits_remaining' => max(0, $requiredCredits - $earnedCredits),
            'completion_percent' => $percent,
            'audit_passed' => $earnedCredits >= $requiredCredits && $remaining === [],
            'cgpa' => $cgpa,
            'at_risk' => $cgpa > 0 && $cgpa < 2.0,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'remaining' => $remaining,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $remaining
     * @param  list<string>  $completedCourseIds
     * @return list<array<string, mixed>>
     */
    private function recommend(array $remaining, array $completedCourseIds): array
    {
        if ($remaining === []) {
            return [];
        }

        $completedSet = array_flip($completedCourseIds);
        $candidates = collect($remaining)->take(12);

        return $candidates
            ->filter(function (array $row) use ($completedSet): bool {
                $prereqs = CoursePrerequisite::query()
                    ->where('course_id', $row['course_id'])
                    ->pluck('prerequisite_course_id');

                foreach ($prereqs as $prereqId) {
                    if (! isset($completedSet[(string) $prereqId])) {
                        return false;
                    }
                }

                return true;
            })
            ->take(5)
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function mapRequirement(CurriculumCourse $item): array
    {
        return [
            'curriculum_course_id' => $item->id,
            'course_id' => $item->course_id,
            'course_code' => $item->course?->code,
            'course_title' => $item->course?->title,
            'credits' => $item->course?->credits,
            'year_level' => $item->year_level,
            'semester' => $item->semester,
            'course_type' => $item->course_type,
        ];
    }
}
