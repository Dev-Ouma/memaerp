<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Examination\Models\StudentMark;
use App\Modules\Examination\Models\TermGpa;
use App\Modules\Examination\Services\ExamCardService;
use App\Modules\Examination\Services\MarksWorkflowService;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ExaminationController extends Controller
{
    public function __construct(
        private readonly MarksWorkflowService $marks,
        private readonly ExamCardService $examCards,
    ) {}

    public function marksSheet(Request $request, string $offeringId): JsonResponse
    {
        $this->requirePermission($this->actor($request), 'examination.marks.view');

        $enrollments = CourseEnrollment::query()
            ->where('course_offering_id', $offeringId)
            ->where('status', 'ENROLLED')
            ->with(['student.person', 'courseOffering.course', 'mark.submitter'])
            ->orderBy('enrolled_at')
            ->get();

        $rows = $enrollments->map(function (CourseEnrollment $enrollment): array {
            $mark = $enrollment->mark;

            return [
                'id' => $mark?->id ?? $enrollment->id,
                'course_enrollment_id' => $enrollment->id,
                'institution_id' => $enrollment->institution_id,
                'cat_score' => $mark?->cat_score,
                'exam_score' => $mark?->exam_score,
                'total_score' => $mark?->total_score,
                'letter_grade' => $mark?->letter_grade,
                'grade_points' => $mark?->grade_points,
                'is_submitted' => $mark?->is_submitted ?? false,
                'approval_status' => $mark?->approval_status ?? 'DRAFT',
                'course_enrollment' => $enrollment,
                'submitter' => $mark?->submitter,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function saveMarks(Request $request, string $offeringId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'examination.marks.enter');

        $validated = $request->validate([
            'enrollment_id' => ['required', 'uuid'],
            'cat_score' => ['required', 'numeric', 'min:0', 'max:40'],
            'exam_score' => ['required', 'numeric', 'min:0', 'max:60'],
        ]);

        $enrollment = CourseEnrollment::query()
            ->where('course_offering_id', $offeringId)
            ->findOrFail($validated['enrollment_id']);

        $mark = $this->marks->saveDraft($enrollment, $validated, $user);

        return response()->json(['data' => $mark]);
    }

    public function submitMarks(Request $request, string $offeringId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'examination.marks.enter');

        $validated = $request->validate(['enrollment_id' => ['required', 'uuid']]);
        $enrollment = CourseEnrollment::query()->where('course_offering_id', $offeringId)->findOrFail($validated['enrollment_id']);

        return response()->json(['data' => $this->marks->submit($enrollment, $user)]);
    }

    public function approveMarks(Request $request, string $offeringId): JsonResponse
    {
        $user = $this->actor($request);
        $validated = $request->validate([
            'enrollment_id' => ['required', 'uuid'],
            'stage' => ['required', 'string', 'in:MODERATE,VERIFY,BOARD'],
        ]);

        $this->requireAnyPermission($user, ['examination.marks.moderate', 'examination.marks.verify', 'examination.marks.approve']);
        $enrollment = CourseEnrollment::query()->where('course_offering_id', $offeringId)->findOrFail($validated['enrollment_id']);

        return response()->json(['data' => $this->marks->approve($enrollment, $user, $validated['stage'])]);
    }

    public function myCard(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'examination.marks.view');

        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();
        $term = Term::query()->where('institution_id', $user->institution_id)
            ->when($request->query('term_id'), fn ($q, $id) => $q->whereKey($id), fn ($q) => $q->current())
            ->firstOrFail();

        return $this->examCards->renderPdf($student, $term);
    }

    public function verifyCard(string $token): JsonResponse
    {
        $card = $this->examCards->verify($token);
        if ($card === null) {
            return response()->json(['valid' => false, 'message' => 'Exam card is invalid or expired.'], 404);
        }

        return response()->json([
            'valid' => true,
            'data' => [
                'student_number' => $card->student?->student_number,
                'full_name' => $card->student?->person?->full_name,
                'term' => $card->term?->name,
                'issued_at' => $card->issued_at?->toISOString(),
            ],
        ]);
    }

    public function termGpas(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'examination.marks.view');

        $query = TermGpa::query()->where('institution_id', $user->institution_id);

        if ($this->isSelfScopedStudent($user)) {
            $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();
            $query->where('student_id', $student->id)->where('is_published', true);
        }

        $gpas = $query->with(['student.person', 'student.programme', 'term.academicYear'])->get();

        return response()->json(['data' => $gpas]);
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function requirePermission(User $user, string $permission): void
    {
        if ($user->scopesFor($permission) === []) {
            throw new AuthorizationException;
        }
    }

    /** @param list<string> $permissions */
    private function requireAnyPermission(User $user, array $permissions): void
    {
        foreach ($permissions as $permission) {
            if ($user->scopesFor($permission) !== []) {
                return;
            }
        }

        throw new AuthorizationException;
    }

    private function isSelfScopedStudent(User $user): bool
    {
        $scopes = $user->scopesFor('examination.marks.view');
        if ($scopes === []) {
            return false;
        }

        return collect($scopes)->every(fn ($scope) => $scope->isSelf());
    }
}
