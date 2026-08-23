<?php

declare(strict_types=1);

namespace App\Modules\Enrollment\Services;

use App\Modules\Course\Models\CourseOffering;
use App\Modules\Course\Models\CoursePrerequisite;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Enrollment\Models\TermRegistration;
use App\Modules\Finance\Services\ClearanceService;
use App\Modules\Finance\Services\FinanceService;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class EnrollmentWorkflowService
{
    private const MAX_CREDITS_PER_TERM = 24;

    public function __construct(
        private readonly ClearanceService $clearance,
        private readonly FinanceService $finance,
    ) {}

    public function registerTerm(Student $student, Term $term): TermRegistration
    {
        if (! $term->registrationIsOpen()) {
            throw ValidationException::withMessages([
                'term' => ['Registration is not open for this term.'],
            ]);
        }

        if (! $this->clearance->registrationCleared($student, $term->id)) {
            abort(response()->json([
                'error' => [
                    'code' => 'ERR-FIN-REG',
                    'message' => 'Registration requires at least '.ClearanceService::REGISTRATION_THRESHOLD.'% fee payment clearance.',
                ],
            ], 402));
        }

        $this->finance->issueTermInvoice($student, $term);

        return TermRegistration::query()->firstOrCreate(
            [
                'institution_id' => $student->institution_id,
                'student_id' => $student->id,
                'term_id' => $term->id,
            ],
            [
                'year_level' => $student->current_year_level,
                'semester' => $student->current_semester,
                'financial_clearance_status' => true,
                'status' => 'REGISTERED',
                'registered_at' => now(),
            ],
        );
    }

    /** @return Collection<int, CourseOffering> */
    public function availableCourses(Student $student, Term $term): Collection
    {
        return CourseOffering::query()
            ->where('institution_id', $student->institution_id)
            ->where('term_id', $term->id)
            ->where('campus_id', $student->campus_id)
            ->where('status', 'OFFERED')
            ->where('is_open_for_enrollment', true)
            ->with(['course'])
            ->orderBy('section_code')
            ->get()
            ->filter(fn (CourseOffering $offering): bool => $this->prerequisitesMet($student, $offering));
    }

    /**
     * @param  list<string>  $offeringIds
     * @return list<CourseEnrollment>
     */
    public function enrollCourses(Student $student, Term $term, array $offeringIds, ?User $actor = null): array
    {
        if (! $term->registrationIsOpen() && ! $term->addDropIsOpen()) {
            throw ValidationException::withMessages(['term' => ['Course enrollment is closed for this term.']]);
        }

        $registration = TermRegistration::query()
            ->where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->first();

        if ($registration === null) {
            throw ValidationException::withMessages(['registration' => ['Register for the term before enrolling in courses.']]);
        }

        $offerings = CourseOffering::query()
            ->with('course')
            ->whereIn('id', $offeringIds)
            ->where('term_id', $term->id)
            ->get();

        $projectedCredits = $this->currentCredits($student, $term) + $offerings->sum(fn ($o) => $o->course->credits);
        if ($projectedCredits > self::MAX_CREDITS_PER_TERM) {
            throw ValidationException::withMessages([
                'credits' => ['Enrollment would exceed the maximum of '.self::MAX_CREDITS_PER_TERM.' credits for this term.'],
            ]);
        }

        return DB::transaction(function () use ($student, $registration, $offerings): array {
            $created = [];
            foreach ($offerings as $offering) {
                if (! $this->prerequisitesMet($student, $offering)) {
                    throw ValidationException::withMessages([
                        'prerequisites' => ["Prerequisites not met for {$offering->course->code}."],
                    ]);
                }

                $created[] = CourseEnrollment::query()->firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'course_offering_id' => $offering->id,
                    ],
                    [
                        'institution_id' => $student->institution_id,
                        'term_registration_id' => $registration->id,
                        'status' => 'ENROLLED',
                        'is_retake' => false,
                        'enrolled_at' => now(),
                    ],
                );
            }

            return $created;
        });
    }

    public function dropCourse(Student $student, CourseEnrollment $enrollment): CourseEnrollment
    {
        $term = $enrollment->courseOffering?->term;
        if ($term !== null && ! $term->addDropIsOpen()) {
            throw ValidationException::withMessages(['term' => ['The add/drop window has closed.']]);
        }

        $enrollment->load('courseOffering.term');
        abort_unless($enrollment->student_id === $student->id, 404);
        $enrollment->forceFill(['status' => 'DROPPED'])->save();

        return $enrollment->fresh(['courseOffering.course']) ?? $enrollment;
    }

    /** @return Collection<int, CourseEnrollment> */
    public function myCourses(Student $student, ?string $termId = null): Collection
    {
        return CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->when($termId !== null, fn ($q) => $q->whereHas('courseOffering', fn ($o) => $o->where('term_id', $termId)))
            ->where('status', 'ENROLLED')
            ->with(['courseOffering.course', 'courseOffering.term', 'mark'])
            ->get();
    }

    public function registrationSlip(TermRegistration $registration): Response
    {
        $registration->load([
            'student.person',
            'student.programme',
            'term.academicYear',
            'courseEnrollments.courseOffering.course',
        ]);

        return Pdf::loadView('reports.registration-slip', ['registration' => $registration])
            ->setPaper('a4')
            ->download('registration-slip-'.str_replace(['/', '\\'], '-', (string) $registration->student?->student_number).'.pdf');
    }

    private function currentCredits(Student $student, Term $term): int
    {
        return (int) CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'ENROLLED')
            ->whereHas('courseOffering', fn ($q) => $q->where('term_id', $term->id))
            ->with('courseOffering.course')
            ->get()
            ->sum(fn (CourseEnrollment $enrollment): int => $enrollment->courseOffering?->course?->credits ?? 0);
    }

    private function prerequisitesMet(Student $student, CourseOffering $offering): bool
    {
        $requirements = CoursePrerequisite::query()
            ->where('course_id', $offering->course_id)
            ->where('requirement_type', 'PREREQUISITE')
            ->whereNull('curriculum_version_id')
            ->pluck('prerequisite_course_id');

        if ($requirements->isEmpty()) {
            return true;
        }

        $passedCourseIds = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'ENROLLED')
            ->whereHas('mark', fn ($q) => $q->where('approval_status', 'SENATE_RATIFIED')->where('total_score', '>=', 40))
            ->with('courseOffering.course')
            ->get()
            ->map(fn (CourseEnrollment $e) => $e->courseOffering?->course_id)
            ->filter()
            ->unique();

        foreach ($requirements as $requiredId) {
            if (! $passedCourseIds->contains($requiredId)) {
                return false;
            }
        }

        return true;
    }
}
