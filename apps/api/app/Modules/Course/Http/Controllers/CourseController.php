<?php

declare(strict_types=1);

namespace App\Modules\Course\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Course\Models\CoursePrerequisite;
use App\Modules\Course\Models\OfferingAllocation;
use App\Modules\Course\Models\OfferingWaitlist;
use App\Modules\Course\Notifications\LecturerAssignedNotification;
use App\Modules\Course\Services\CataloguePrerequisiteService;
use App\Modules\Course\Services\CourseReportService;
use App\Modules\Course\Services\CourseWorkflowService;
use App\Modules\Iam\Models\User;
use App\Modules\Iam\Services\AccessControl;
use App\Modules\Iam\Services\ScopeResolver;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Term;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class CourseController extends Controller
{
    public function __construct(
        private readonly AccessControl $access,
        private readonly ScopeResolver $scopes,
        private readonly CataloguePrerequisiteService $prerequisites,
        private readonly CourseWorkflowService $workflow,
        private readonly CourseReportService $reports,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.view');
        $search = trim($request->string('search')->value());
        $courses = Course::query()
            ->where('institution_id', $user->institution_id)
            ->with(['department.faculty', 'prerequisites.prerequisiteCourse', 'reviews.reviewer'])
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $nested) => $nested
                ->where('code', 'ilike', "%{$search}%")
                ->orWhere('title', 'ilike', "%{$search}%")))
            ->when($request->filled('department_id'), fn (Builder $q) => $q->where('department_id', $request->string('department_id')->value()))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')->value()))
            ->when($request->filled('credits'), fn (Builder $q) => $q->where('credits', $request->integer('credits')))
            ->when($request->has('active'), fn (Builder $q) => $q->where('is_active', $request->boolean('active')))
            ->orderBy('code')
            ->get();

        return response()->json(['data' => $courses]);
    }

    public function show(Request $request, Course $course): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.view');
        $this->assertTenant($course->institution_id, $user);

        return response()->json(['data' => $course->load($this->courseRelations())]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.manage');
        $validated = $request->validate($this->courseRules($user));
        $this->assertDepartmentAccess($user, 'course.catalogue.manage', $validated['department_id']);
        if (Course::query()->where('institution_id', $user->institution_id)->where('code', $validated['code'])->exists()) {
            abort(response()->json(['error' => ['code' => 'ERR-CRS-001', 'message' => 'A course with this code already exists.']], 409));
        }
        $course = Course::query()->create($validated + [
            'institution_id' => $user->institution_id,
            'status' => 'DRAFT',
            'is_active' => false,
        ]);

        return response()->json(['message' => 'Course drafted for departmental review.', 'data' => $course->load('department.faculty')], 201);
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.manage');
        $this->assertTenant($course->institution_id, $user);
        $this->assertDepartmentAccess($user, 'course.catalogue.manage', $course->department_id);
        if ($course->status === 'DISCONTINUED') {
            throw ValidationException::withMessages(['status' => ['A discontinued course cannot be edited.']]);
        }
        $rules = $this->courseRules($user);
        foreach ($rules as &$rule) {
            $rule[0] = 'sometimes';
        }
        $validated = $request->validate($rules);
        if (isset($validated['code']) && Course::query()->where('institution_id', $user->institution_id)->where('code', $validated['code'])->whereKeyNot($course->id)->exists()) {
            abort(response()->json(['error' => ['code' => 'ERR-CRS-001', 'message' => 'A course with this code already exists.']], 409));
        }
        $course->auditReason('Master course catalogue record updated')->update($validated);

        return response()->json(['message' => 'Course updated.', 'data' => $course->fresh($this->courseRelations())]);
    }

    public function submit(Request $request, Course $course): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.manage');
        $this->assertTenant($course->institution_id, $user);
        $this->assertDepartmentAccess($user, 'course.catalogue.manage', $course->department_id);

        return response()->json(['message' => 'Course submitted for department board review.', 'data' => $this->workflow->submit($course)]);
    }

    public function approve(Request $request, Course $course): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.approve');
        $this->assertTenant($course->institution_id, $user);
        $this->assertDepartmentAccess($user, 'course.catalogue.approve', $course->department_id);
        $validated = $request->validate([
            'stage' => ['required', Rule::in(array_keys(CourseWorkflowService::STAGES))],
            'reference' => ['required', 'string', 'min:3', 'max:128'],
            'comments' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);
        $updated = $this->workflow->approveNext($course, $user, $validated['stage'], $validated['reference'], $validated['comments'] ?? null);

        return response()->json([
            'message' => $updated->status === 'ACTIVE' ? 'Course approved and published to the catalogue.' : "{$validated['stage']} approval recorded.",
            'data' => $updated,
        ]);
    }

    public function addPrerequisite(Request $request, Course $course): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.manage');
        $this->assertTenant($course->institution_id, $user);
        $this->assertDepartmentAccess($user, 'course.catalogue.manage', $course->department_id);
        $validated = $request->validate([
            'required_course_id' => ['required', 'uuid'],
            'requirement_type' => ['required', Rule::in(['PREREQUISITE', 'COREQUISITE', 'ANTIREQUISITE'])],
        ]);
        $requirement = $this->prerequisites->add($course, $validated['required_course_id'], $validated['requirement_type']);

        return response()->json(['message' => 'Course requirement added.', 'data' => $requirement->load(['course', 'prerequisiteCourse'])], 201);
    }

    public function destroyPrerequisite(Request $request, Course $course, CoursePrerequisite $requirement): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.manage');
        $this->assertTenant($course->institution_id, $user);
        $this->assertDepartmentAccess($user, 'course.catalogue.manage', $course->department_id);
        abort_unless($requirement->course_id === $course->id && $requirement->curriculum_version_id === null, 404);
        $requirement->auditReason('Catalogue course requirement removed')->delete();

        return response()->json(['message' => 'Course requirement removed.']);
    }

    public function offerings(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.view');
        $query = CourseOffering::query()->with($this->offeringRelations());
        $this->access->scopeQuery($query, $user, 'course.offering.view');
        $query->when($request->filled('term_id'), fn (Builder $q) => $q->where('term_id', $request->string('term_id')->value()))
            ->when($request->filled('campus_id'), fn (Builder $q) => $q->where('campus_id', $request->string('campus_id')->value()))
            ->when($request->filled('department_id'), fn (Builder $q) => $q->where('department_id', $request->string('department_id')->value()))
            ->when($request->boolean('active_only'), fn (Builder $q) => $q->where('is_open_for_enrollment', true)->where('status', 'OFFERED'));

        return response()->json(['data' => $this->presentOfferings($query->orderBy('section_code')->get())]);
    }

    public function activeOfferings(Request $request): JsonResponse
    {
        $request->merge(['active_only' => true]);

        return $this->offerings($request);
    }

    public function storeOffering(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.manage');
        $validated = $request->validate([
            'course_id' => ['required', 'uuid', Rule::exists(Course::class, 'id')->where('institution_id', $user->institution_id)],
            'term_id' => ['required', 'uuid', Rule::exists(Term::class, 'id')->where('institution_id', $user->institution_id)],
            'campus_id' => ['required', 'uuid', Rule::exists(Campus::class, 'id')->where('institution_id', $user->institution_id)],
            'section_code' => ['required', 'string', 'max:10'],
            'max_capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'delivery_mode' => ['sometimes', Rule::in(['IN_PERSON', 'ONLINE', 'HYBRID'])],
            'workload_credits' => ['sometimes', 'integer', 'min:0', 'max:24'],
        ]);
        $course = Course::query()->with('department')->whereKey($validated['course_id'])->firstOrFail();
        if ($course->status !== 'ACTIVE') {
            throw ValidationException::withMessages(['course_id' => ['Only an approved catalogue course can be offered.']]);
        }
        $this->assertDepartmentAccess($user, 'course.offering.manage', $course->department_id);
        $department = $course->department;
        if (! $department instanceof Department) {
            throw ValidationException::withMessages(['course_id' => ['The course must belong to a department.']]);
        }
        if (CourseOffering::query()->where([
            'course_id' => $validated['course_id'],
            'term_id' => $validated['term_id'],
            'campus_id' => $validated['campus_id'],
            'section_code' => $validated['section_code'],
        ])->exists()) {
            throw ValidationException::withMessages(['section_code' => ['That section already exists for this campus and term.']]);
        }
        $offering = CourseOffering::query()->create($validated + [
            'institution_id' => $user->institution_id,
            'department_id' => $department->id,
            'faculty_id' => $department->faculty_id,
            'enrolled_count' => 0,
            'waitlist_count' => 0,
            'status' => 'OFFERED',
            'is_open_for_enrollment' => true,
        ]);

        return response()->json(['message' => 'Semester offering opened.', 'data' => $this->presentOffering($offering->load($this->offeringRelations()))], 201);
    }

    public function assignLecturer(Request $request, CourseOffering $offering): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.assign-lecturer');
        $this->assertTenant($offering->institution_id, $user);
        if ($this->access->denies($user, 'course.offering.assign-lecturer', $offering)) {
            throw new AuthorizationException;
        }
        $validated = $request->validate([
            'lecturer_id' => ['required', 'uuid', Rule::exists(User::class, 'id')->where('institution_id', $user->institution_id)],
            'role' => ['sometimes', Rule::in(['PRIMARY', 'ASSISTANT'])],
            'workload_credits' => ['sometimes', 'integer', 'min:0', 'max:24'],
        ]);
        $role = $validated['role'] ?? 'PRIMARY';
        $workload = $validated['workload_credits'] ?? $offering->workload_credits;
        $lecturer = User::query()->with('person')->whereKey($validated['lecturer_id'])->firstOrFail();

        $updated = DB::transaction(function () use ($offering, $lecturer, $role, $workload): CourseOffering {
            OfferingAllocation::query()->updateOrCreate(
                ['course_offering_id' => $offering->id, 'lecturer_id' => $lecturer->id],
                ['institution_id' => $offering->institution_id, 'role' => $role, 'workload_credits' => $workload],
            );
            if ($role === 'PRIMARY') {
                $offering->auditReason('Primary lecturer allocated')->update([
                    'lecturer_id' => $lecturer->id,
                    'workload_credits' => $workload,
                ]);
            }

            return $offering->fresh($this->offeringRelations()) ?? $offering;
        });
        Notification::send($lecturer, new LecturerAssignedNotification($updated));

        return response()->json(['message' => 'Lecturer allocated to the section.', 'data' => $this->presentOffering($updated)]);
    }

    public function closeOffering(Request $request, CourseOffering $offering): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.manage');
        $this->assertTenant($offering->institution_id, $user);
        if ($this->access->denies($user, 'course.offering.manage', $offering)) {
            throw new AuthorizationException;
        }
        $offering->auditReason('Enrollment window closed')->update([
            'status' => 'CLOSED',
            'is_open_for_enrollment' => false,
            'closed_at' => now(),
        ]);

        return response()->json(['message' => 'Offering closed for enrollment.', 'data' => $this->presentOffering($offering->fresh($this->offeringRelations()) ?? $offering)]);
    }

    public function joinWaitlist(Request $request, CourseOffering $offering): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.manage');
        $this->assertTenant($offering->institution_id, $user);
        $validated = $request->validate([
            'student_id' => ['required', 'uuid'],
        ]);
        $studentExists = DB::table('student.students')
            ->where('institution_id', $user->institution_id)
            ->where('id', $validated['student_id'])
            ->exists();
        if (! $studentExists) {
            throw ValidationException::withMessages(['student_id' => ['The selected student is invalid.']]);
        }
        if ($offering->enrolled_count < $offering->max_capacity && $offering->is_open_for_enrollment) {
            throw ValidationException::withMessages(['offering' => ['Seats remain on this section; enroll the student instead of waitlisting.']]);
        }
        $entry = DB::transaction(function () use ($offering, $validated): OfferingWaitlist {
            $locked = CourseOffering::query()->whereKey($offering->id)->lockForUpdate()->firstOrFail();
            $entry = OfferingWaitlist::query()->firstOrCreate(
                ['course_offering_id' => $locked->id, 'student_id' => $validated['student_id']],
                [
                    'institution_id' => $locked->institution_id,
                    'position' => $locked->waitlist()->where('status', 'WAITING')->max('position') + 1,
                    'status' => 'WAITING',
                ],
            );
            $locked->update(['waitlist_count' => $locked->waitlist()->where('status', 'WAITING')->count()]);

            return $entry;
        });

        return response()->json(['message' => 'Student added to the waitlist.', 'data' => $entry], 201);
    }

    public function lecturers(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.view');
        $staff = User::query()->where('institution_id', $user->institution_id)->active()
            ->whereHas('roleAssignments.role', fn (Builder $q) => $q->whereIn('code', ['lecturer', 'head-of-department', 'dean', 'trainer']))
            ->with('person')
            ->orderBy('email')
            ->get(['id', 'institution_id', 'person_id', 'email']);

        return response()->json(['data' => $staff]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, ['course.catalogue.view', 'course.offering.view']);
        $offerings = $this->access->scopeQuery(CourseOffering::query()->with('allocations'), $user, 'course.offering.view')->get();
        $open = $offerings->where('status', 'OFFERED');
        $saturated = $open->filter(fn (CourseOffering $offering): bool => $offering->max_capacity > 0 && ($offering->enrolled_count / $offering->max_capacity) >= 0.9);

        return response()->json(['data' => [
            'active_courses' => Course::query()->where('institution_id', $user->institution_id)->where('status', 'ACTIVE')->count(),
            'draft_courses' => Course::query()->where('institution_id', $user->institution_id)->where('status', 'DRAFT')->count(),
            'open_sections' => $open->count(),
            'closed_sections' => $offerings->where('status', 'CLOSED')->count(),
            'capacity_saturation_percent' => $open->sum('max_capacity') > 0
                ? round(($open->sum('enrolled_count') / $open->sum('max_capacity')) * 100, 1)
                : 0,
            'saturated_sections' => $saturated->count(),
            'lecturer_workload_hours' => $offerings->sum('workload_credits'),
        ]]);
    }

    public function catalogueReport(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.view');
        $format = $request->validate(['format' => ['sometimes', Rule::in(['pdf', 'csv'])]])['format'] ?? 'pdf';
        $courses = Course::query()->where('institution_id', $user->institution_id)->with('department')->orderBy('code')->get();

        return $this->reports->catalogue($courses, $format);
    }

    public function offeringReport(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.offering.view');
        $format = $request->validate(['format' => ['sometimes', Rule::in(['pdf', 'csv'])]])['format'] ?? 'pdf';
        $query = CourseOffering::query()->with($this->offeringRelations());
        $this->access->scopeQuery($query, $user, 'course.offering.view');

        return $this->reports->sections($query->orderBy('section_code')->get(), $format);
    }

    public function syllabus(Request $request, Course $course): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'course.catalogue.view');
        $this->assertTenant($course->institution_id, $user);

        return $this->reports->syllabus($course->load(['department.faculty', 'prerequisites.prerequisiteCourse']));
    }

    /** @return array<string, list<mixed>> */
    private function courseRules(User $user): array
    {
        return [
            'department_id' => ['required', 'uuid', Rule::exists(Department::class, 'id')->where('institution_id', $user->institution_id)],
            'code' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string'],
            'credits' => ['required', 'integer', 'min:1', 'max:12'],
            'lecture_hours' => ['sometimes', 'integer', 'min:0', 'max:20'],
            'lab_hours' => ['sometimes', 'integer', 'min:0', 'max:20'],
            'tutorial_hours' => ['sometimes', 'integer', 'min:0', 'max:20'],
            'learning_outcomes' => ['sometimes', 'nullable', 'string'],
            'syllabus_outline' => ['sometimes', 'nullable', 'string'],
        ];
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

    private function assertTenant(mixed $institutionId, User $user): void
    {
        abort_unless(is_string($institutionId) && $institutionId === $user->institution_id, 404);
    }

    private function assertDepartmentAccess(User $user, string $permission, mixed $departmentId): void
    {
        abort_unless(is_string($departmentId), 404);
        $resolved = $this->scopes->resolve($user, $permission);
        if ($resolved->isEmpty()) {
            throw new AuthorizationException;
        }
        if ($resolved->institutionWide) {
            return;
        }
        $department = Department::query()->whereKey($departmentId)->first();
        if (! $department instanceof Department) {
            throw new AuthorizationException;
        }
        if (in_array($department->id, $resolved->departmentIds, true) || in_array($department->faculty_id, $resolved->facultyIds, true)) {
            return;
        }

        throw new AuthorizationException;
    }

    /** @return list<string> */
    private function courseRelations(): array
    {
        return ['department.faculty', 'prerequisites.prerequisiteCourse', 'reviews.reviewer', 'offerings.term', 'offerings.campus', 'offerings.lecturer.person'];
    }

    /** @return list<string> */
    private function offeringRelations(): array
    {
        return ['course.department', 'term.academicYear', 'campus', 'lecturer.person', 'allocations.lecturer.person', 'waitlist'];
    }

    /** @param \Illuminate\Support\Collection<int, CourseOffering> $offerings */
    private function presentOfferings(\Illuminate\Support\Collection $offerings): \Illuminate\Support\Collection
    {
        return $offerings->map(fn (CourseOffering $offering): CourseOffering => $this->presentOffering($offering));
    }

    private function presentOffering(CourseOffering $offering): CourseOffering
    {
        $offering->setAttribute('capacity', $offering->max_capacity);

        return $offering;
    }
}
