<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Examination\Models\TermGpa;
use App\Modules\Institution\Models\GradingScale;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Illuminate\Support\Collection;

final class GpaCalculationService
{
    public function calculateForTerm(Student $student, Term $term): TermGpa
    {
        $enrollments = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'ENROLLED')
            ->whereHas('courseOffering', fn ($q) => $q->where('term_id', $term->id))
            ->with(['courseOffering.course', 'mark'])
            ->get();

        $scale = GradingScale::query()
            ->where('institution_id', $student->institution_id)
            ->effectiveOn(now())
            ->with('bands')
            ->first();

        $attempted = 0;
        $earned = 0;
        $gradePoints = 0.0;

        foreach ($enrollments as $enrollment) {
            $mark = $enrollment->mark;
            if ($mark === null || $mark->approval_status !== 'SENATE_RATIFIED') {
                continue;
            }

            $credits = (int) ($enrollment->courseOffering?->course?->credits ?? 0);
            $attempted += $credits;

            if ((float) $mark->total_score >= 40) {
                $earned += $credits;
            }

            $gp = $mark->grade_points;
            if ($gp === null && $scale !== null) {
                $band = $scale->bandFor((float) $mark->total_score);
                $gp = $band?->grade_point;
                if ($band !== null) {
                    $mark->forceFill(['letter_grade' => $band->letter, 'grade_points' => $band->grade_point])->save();
                }
            }

            $gradePoints += ((float) ($gp ?? 0)) * $credits;
        }

        $gpa = $attempted > 0 ? round($gradePoints / $attempted, 2) : 0.0;
        $previous = TermGpa::query()->where('student_id', $student->id)->whereKeyNot($term->id)->orderByDesc('calculated_at')->first();
        $prevAttempted = (int) ($previous?->credits_attempted ?? 0);
        $prevEarned = (int) ($previous?->credits_earned ?? 0);
        $prevPoints = ((float) ($previous?->gpa ?? 0)) * max(1, $prevAttempted);
        $totalAttempted = $prevAttempted + $attempted;
        $cgpa = $totalAttempted > 0 ? round(($prevPoints + $gradePoints) / $totalAttempted, 2) : $gpa;

        $standing = $gpa >= 2.0 ? 'GOOD_STANDING' : 'PROBATION';
        $decision = $gpa >= 2.0 ? 'PROGRESS' : 'PROBATION';

        return TermGpa::query()->updateOrCreate(
            ['student_id' => $student->id, 'term_id' => $term->id],
            [
                'institution_id' => $student->institution_id,
                'credits_attempted' => $attempted,
                'credits_earned' => $earned,
                'gpa' => $gpa,
                'cgpa' => $cgpa,
                'academic_standing' => $standing,
                'progression_decision' => $decision,
                'calculated_at' => now(),
                'is_published' => false,
            ],
        );
    }

    /** @return Collection<int, TermGpa> */
    public function calculateBatch(string $institutionId, string $termId): Collection
    {
        $term = Term::query()->where('institution_id', $institutionId)->findOrFail($termId);
        $students = Student::query()->where('institution_id', $institutionId)->where('status', 'ACTIVE')->get();

        return $students->map(fn (Student $student) => $this->calculateForTerm($student, $term));
    }
}
