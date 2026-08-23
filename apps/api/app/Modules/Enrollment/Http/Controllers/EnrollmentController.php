<?php

declare(strict_types=1);

namespace App\Modules\Enrollment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Enrollment\Models\TermRegistration;
use App\Modules\Enrollment\Services\EnrollmentWorkflowService;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnrollmentController extends Controller
{
    public function __construct(private readonly EnrollmentWorkflowService $workflow) {}

    public function registerTerm(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'enrollment.registration.register');

        $validated = $request->validate(['term_id' => ['required', 'uuid']]);
        $student = $this->resolveStudent($user, $request->input('student_id'));
        $term = Term::query()->where('institution_id', $user->institution_id)->findOrFail($validated['term_id']);

        $registration = $this->workflow->registerTerm($student, $term);

        return response()->json(['data' => $registration->load(['term', 'student.person'])], 201);
    }

    public function availableCourses(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'enrollment.registration.view');

        $validated = $request->validate(['term_id' => ['required', 'uuid']]);
        $student = $this->resolveStudent($user, $request->input('student_id'));
        $term = Term::query()->where('institution_id', $user->institution_id)->findOrFail($validated['term_id']);

        return response()->json(['data' => $this->workflow->availableCourses($student, $term)]);
    }

    public function enrollCourses(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, ['enrollment.registration.register', 'enrollment.registration.register-on-behalf']);

        $validated = $request->validate([
            'term_id' => ['required', 'uuid'],
            'offering_ids' => ['required', 'array', 'min:1'],
            'offering_ids.*' => ['uuid', 'distinct'],
            'student_id' => ['sometimes', 'uuid'],
        ]);

        $student = $this->resolveStudent($user, $validated['student_id'] ?? null);
        $term = Term::query()->where('institution_id', $user->institution_id)->findOrFail($validated['term_id']);
        $enrollments = $this->workflow->enrollCourses($student, $term, $validated['offering_ids'], $user);

        return response()->json(['data' => collect($enrollments)->load(['courseOffering.course'])], 201);
    }

    public function dropCourse(Request $request, string $enrollmentId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'enrollment.registration.register');

        $enrollment = CourseEnrollment::query()->where('institution_id', $user->institution_id)->findOrFail($enrollmentId);
        $student = $this->resolveStudent($user, null);
        abort_unless($enrollment->student_id === $student->id || $user->scopesFor('enrollment.registration.register-on-behalf') !== [], 403);

        if ($user->scopesFor('enrollment.registration.register-on-behalf') !== [] && $request->filled('student_id')) {
            $student = Student::query()->findOrFail((string) $request->input('student_id'));
        }

        return response()->json(['data' => $this->workflow->dropCourse($student, $enrollment)]);
    }

    public function myCourses(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'enrollment.registration.view');
        $student = $this->resolveStudent($user, $request->input('student_id'));

        return response()->json([
            'data' => $this->workflow->myCourses($student, $request->query('term_id')),
        ]);
    }

    public function registrationSlip(Request $request, string $registrationId): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'enrollment.registration.view');

        $registration = TermRegistration::query()
            ->where('institution_id', $user->institution_id)
            ->findOrFail($registrationId);

        return $this->workflow->registrationSlip($registration);
    }

    public function students(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'student.record.view');

        $students = Student::query()
            ->where('institution_id', $user->institution_id)
            ->with(['person', 'programme', 'campus'])
            ->get();

        return response()->json(['data' => $students]);
    }

    public function showStudent(Request $request, string $id): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'student.record.view');

        $student = Student::query()
            ->with([
                'person', 'programme', 'campus',
                'termRegistrations.term.academicYear',
                'termRegistrations.courseEnrollments.courseOffering.course',
                'termRegistrations.courseEnrollments.mark',
            ])
            ->findOrFail($id);

        return response()->json(['data' => $student]);
    }

    public function termRegistrations(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'enrollment.registration.view');

        $registrations = TermRegistration::query()
            ->where('institution_id', $user->institution_id)
            ->with(['student.person', 'term.academicYear', 'courseEnrollments.courseOffering.course'])
            ->get();

        return response()->json(['data' => $registrations]);
    }

    public function courseEnrollments(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'enrollment.registration.view');

        $enrollments = CourseEnrollment::query()
            ->where('institution_id', $user->institution_id)
            ->with(['student.person', 'courseOffering.course', 'courseOffering.term', 'mark'])
            ->get();

        return response()->json(['data' => $enrollments]);
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

    private function resolveStudent(User $user, mixed $studentId): Student
    {
        if ($user->scopesFor('enrollment.registration.register-on-behalf') !== [] && is_string($studentId) && $studentId !== '') {
            return Student::query()->where('institution_id', $user->institution_id)->findOrFail($studentId);
        }

        return Student::query()->where('person_id', $user->person_id)->firstOrFail();
    }
}
