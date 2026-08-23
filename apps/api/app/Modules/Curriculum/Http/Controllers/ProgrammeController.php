<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CoursePrerequisite;
use App\Modules\Curriculum\Models\CurriculumCourse;
use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Curriculum\Models\ElectiveGroup;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Curriculum\Notifications\CurriculumApprovedNotification;
use App\Modules\Curriculum\Services\CurriculumReportService;
use App\Modules\Curriculum\Services\CurriculumWorkflowService;
use App\Modules\Curriculum\Services\PrerequisiteGraphService;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Department;
use App\Modules\Student\Models\Student;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class ProgrammeController extends Controller
{
    public function __construct(
        private readonly CurriculumWorkflowService $workflow,
        private readonly PrerequisiteGraphService $prerequisites,
        private readonly CurriculumReportService $reports,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.view');
        $search = trim($request->string('search')->value());
        $programmes = Programme::query()->where('institution_id', $user->institution_id)
            ->with(['department.faculty', 'versions.effectiveYear'])
            ->when($search !== '', fn ($q) => $q->where(fn ($nested) => $nested->where('name', 'ilike', "%{$search}%")->orWhere('code', 'ilike', "%{$search}%")))
            ->when($request->filled('award_level'), fn ($q) => $q->where('award_level', $request->string('award_level')->value()))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->string('department_id')->value()))
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->boolean('active')))
            ->orderBy('name')->get()
            ->each(function (Programme $programme): void {
                $expiresAt = $programme->getAttribute('accreditation_expires_on');
                $programme->setAttribute(
                    'accreditation_warning',
                    $expiresAt instanceof CarbonInterface && $expiresAt->lte(now()->addMonths(6)),
                );
            });

        return response()->json(['data' => $programmes]);
    }

    public function show(Request $request, Programme $programme): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.view');
        $this->assertTenant($programme->institution_id, $user);

        return response()->json(['data' => $programme->load(['department.faculty', 'versions' => fn ($q) => $q->with($this->versionRelations())->orderByDesc('created_at')])]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $validated = $request->validate($this->programmeRules($user));
        $programme = Programme::query()->create($validated + ['institution_id' => $user->institution_id, 'status' => 'ACTIVE', 'is_active' => true]);

        return response()->json(['message' => 'Programme created successfully.', 'data' => $programme->load('department.faculty')], 201);
    }

    public function update(Request $request, Programme $programme): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertTenant($programme->institution_id, $user);
        $rules = $this->programmeRules($user, $programme);
        foreach ($rules as &$rule) {
            $rule[0] = 'sometimes';
        }
        $validated = $request->validate($rules + ['is_active' => ['sometimes', 'boolean']]);
        $programme->auditReason('Programme registry record updated')->update($validated);

        return response()->json(['message' => 'Programme updated.', 'data' => $programme->fresh('department.faculty')]);
    }

    public function versions(Request $request, Programme $programme): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.view');
        $this->assertTenant($programme->institution_id, $user);

        return response()->json(['data' => $programme->versions()->with($this->versionRelations())->orderByDesc('created_at')->get()]);
    }

    public function storeVersion(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $validated = $request->validate([
            'programme_id' => ['required', 'uuid', Rule::exists(Programme::class, 'id')->where('institution_id', $user->institution_id)],
            'effective_year_id' => ['required', 'uuid', Rule::exists(AcademicYear::class, 'id')->where('institution_id', $user->institution_id)],
            'version_code' => ['required', 'string', 'max:50', Rule::unique(CurriculumVersion::class, 'version_code')->where('programme_id', $request->input('programme_id'))],
            'graduation_credits_required' => ['required', 'integer', 'min:1', 'max:1000'],
            'minimum_elective_credits' => ['sometimes', 'integer', 'min:0', 'max:1000'],
        ]);
        $version = CurriculumVersion::query()->create($validated + ['institution_id' => $user->institution_id, 'status' => 'DRAFT', 'is_approved' => false]);

        return response()->json(['message' => 'Draft curriculum version created.', 'data' => $version->load($this->versionRelations())], 201);
    }

    public function storeElectiveGroup(Request $request, CurriculumVersion $version): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertMutableVersion($version, $user);
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32', Rule::unique(ElectiveGroup::class, 'code')->where('curriculum_version_id', $version->id)],
            'name' => ['required', 'string', 'max:120'],
            'minimum_courses' => ['required', 'integer', 'min:1', 'max:50'],
            'minimum_credits' => ['required', 'integer', 'min:1', 'max:500'],
        ]);
        $group = ElectiveGroup::query()->create($validated + ['institution_id' => $user->institution_id, 'curriculum_version_id' => $version->id]);

        return response()->json(['message' => 'Elective cluster created.', 'data' => $group], 201);
    }

    public function updateElectiveGroup(Request $request, CurriculumVersion $version, ElectiveGroup $group): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertMutableVersion($version, $user);
        abort_unless($group->curriculum_version_id === $version->id, 404);
        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:32', Rule::unique(ElectiveGroup::class, 'code')->ignore($group->id)->where('curriculum_version_id', $version->id)],
            'name' => ['sometimes', 'string', 'max:120'],
            'minimum_courses' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'minimum_credits' => ['sometimes', 'integer', 'min:1', 'max:500'],
        ]);
        $group->auditReason('Draft elective cluster updated')->update($validated);

        return response()->json(['message' => 'Elective cluster updated.', 'data' => $group->fresh()]);
    }

    public function destroyElectiveGroup(Request $request, CurriculumVersion $version, ElectiveGroup $group): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertMutableVersion($version, $user);
        abort_unless($group->curriculum_version_id === $version->id, 404);
        if ($group->curriculumCourses()->exists()) {
            throw ValidationException::withMessages(['elective_group' => ['Remove or reclassify the courses in this elective cluster first.']]);
        }
        $group->auditReason('Draft elective cluster removed')->delete();

        return response()->json(['message' => 'Elective cluster removed.']);
    }

    public function storeCurriculumCourse(Request $request, CurriculumVersion $version): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertMutableVersion($version, $user);
        $validated = $request->validate([
            'course_id' => ['required', 'uuid', Rule::exists(Course::class, 'id')->where('institution_id', $user->institution_id), Rule::unique(CurriculumCourse::class, 'course_id')->where('curriculum_version_id', $version->id)],
            'year_level' => ['required', 'integer', 'min:1', 'max:10'],
            'semester' => ['required', 'integer', 'min:1', 'max:4'],
            'course_type' => ['required', Rule::in(['CORE', 'ELECTIVE', 'REQUIRED_AUDIT'])],
            'elective_group_id' => ['required_if:course_type,ELECTIVE', 'nullable', 'uuid', Rule::exists(ElectiveGroup::class, 'id')->where('curriculum_version_id', $version->id)],
        ]);
        $item = $version->curriculumCourses()->create($validated + ['institution_id' => $user->institution_id]);

        return response()->json(['message' => 'Course added to the curriculum grid.', 'data' => $item->load(['course', 'electiveGroup'])], 201);
    }

    public function updateCurriculumCourse(Request $request, CurriculumVersion $version, CurriculumCourse $item): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertMutableVersion($version, $user);
        abort_unless($item->curriculum_version_id === $version->id, 404);
        $validated = $request->validate([
            'year_level' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'semester' => ['sometimes', 'integer', 'min:1', 'max:4'],
            'course_type' => ['sometimes', Rule::in(['CORE', 'ELECTIVE', 'REQUIRED_AUDIT'])],
            'elective_group_id' => ['sometimes', 'nullable', 'uuid', Rule::exists(ElectiveGroup::class, 'id')->where('curriculum_version_id', $version->id)],
        ]);
        $type = $validated['course_type'] ?? $item->course_type;
        $groupId = array_key_exists('elective_group_id', $validated) ? $validated['elective_group_id'] : $item->elective_group_id;
        if ($type === 'ELECTIVE' && ! is_string($groupId)) {
            throw ValidationException::withMessages(['elective_group_id' => ['An elective course must belong to an elective cluster.']]);
        }
        if ($type !== 'ELECTIVE') {
            $validated['elective_group_id'] = null;
        }
        $item->auditReason('Draft curriculum grid entry updated')->update($validated);

        return response()->json(['message' => 'Curriculum grid entry updated.', 'data' => $item->fresh(['course', 'electiveGroup'])]);
    }

    public function destroyCurriculumCourse(Request $request, CurriculumVersion $version, CurriculumCourse $item): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertMutableVersion($version, $user);
        abort_unless($item->curriculum_version_id === $version->id, 404);
        DB::transaction(function () use ($version, $item): void {
            $version->requirements()->where(fn ($query) => $query->where('course_id', $item->course_id)->orWhere('prerequisite_course_id', $item->course_id))->delete();
            $item->auditReason('Draft curriculum grid entry removed')->delete();
        });

        return response()->json(['message' => 'Course removed from the curriculum grid.']);
    }

    public function addRequirement(Request $request, CurriculumVersion $version): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertTenant($version->institution_id, $user);
        $validated = $request->validate([
            'course_id' => ['required', 'uuid'],
            'required_course_id' => ['required', 'uuid'],
            'requirement_type' => ['required', Rule::in(['PREREQUISITE', 'COREQUISITE', 'ANTIREQUISITE'])],
        ]);
        $requirement = $this->prerequisites->add($version, $validated['course_id'], $validated['required_course_id'], $validated['requirement_type']);

        return response()->json(['message' => 'Course requirement added.', 'data' => $requirement->load(['course', 'prerequisiteCourse'])], 201);
    }

    public function destroyRequirement(Request $request, CurriculumVersion $version, CoursePrerequisite $requirement): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertMutableVersion($version, $user);
        abort_unless($requirement->curriculum_version_id === $version->id, 404);
        $requirement->auditReason('Draft course requirement removed')->delete();

        return response()->json(['message' => 'Course requirement removed.']);
    }

    public function submit(Request $request, CurriculumVersion $version): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertTenant($version->institution_id, $user);

        return response()->json(['message' => 'Curriculum submitted for HOD review.', 'data' => $this->workflow->submit($version)]);
    }

    public function approve(Request $request, CurriculumVersion $version): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.approve');
        $this->assertTenant($version->institution_id, $user);
        $validated = $request->validate([
            'stage' => ['required', Rule::in(array_keys(CurriculumWorkflowService::STAGES))],
            'reference' => ['required', 'string', 'min:3', 'max:128'],
            'comments' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);
        $updated = $this->workflow->approveNext($version, $user, $validated['stage'], $validated['reference'], $validated['comments'] ?? null);
        if ($updated->status === 'APPROVED') {
            $recipients = User::query()->where('institution_id', $user->institution_id)->active()
                ->whereHas('roleAssignments.role', fn ($q) => $q->whereIn('code', ['dean', 'head-of-department']))->get();
            Notification::send($recipients, new CurriculumApprovedNotification($updated));
        }

        return response()->json(['message' => $updated->status === 'APPROVED' ? 'Curriculum approved and locked by Senate.' : "{$validated['stage']} approval recorded.", 'data' => $updated]);
    }

    public function assignCohort(Request $request, CurriculumVersion $version): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.manage');
        $this->assertTenant($version->institution_id, $user);
        if ($version->status !== 'APPROVED') {
            throw ValidationException::withMessages(['version' => ['Only a Senate-approved curriculum can be assigned to a cohort.']]);
        }
        $validated = $request->validate([
            'admission_year_id' => ['required', 'uuid', Rule::exists(AcademicYear::class, 'id')->where('institution_id', $user->institution_id)],
        ]);
        $assigned = Student::query()
            ->where('institution_id', $user->institution_id)
            ->where('programme_id', $version->programme_id)
            ->where('admission_year_id', $validated['admission_year_id'])
            ->whereNull('curriculum_version_id')
            ->update(['curriculum_version_id' => $version->id, 'updated_at' => now()]);

        return response()->json(['message' => "Curriculum assigned to {$assigned} student records.", 'data' => ['assigned_count' => $assigned]]);
    }

    public function courses(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.view');

        return response()->json(['data' => Course::query()->where('institution_id', $user->institution_id)->where('is_active', true)->orderBy('code')->get()]);
    }

    public function report(Request $request, CurriculumVersion $version): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'curriculum.programme.view');
        $this->assertTenant($version->institution_id, $user);
        $format = $request->validate(['format' => ['sometimes', Rule::in(['pdf', 'csv'])]])['format'] ?? 'pdf';

        return $this->reports->version($version, $format);
    }

    /** @return array<string, list<mixed>> */
    private function programmeRules(User $user, ?Programme $programme = null): array
    {
        return [
            'department_id' => ['required', 'uuid', Rule::exists(Department::class, 'id')->where('institution_id', $user->institution_id)],
            'code' => ['required', 'string', 'max:32', Rule::unique(Programme::class, 'code')->ignore($programme?->id)->where('institution_id', $user->institution_id)],
            'name' => ['required', 'string', 'max:200'],
            'award_level' => ['required', Rule::in(['CERTIFICATE', 'DIPLOMA', 'BACHELORS', 'MASTERS', 'DOCTORATE'])],
            'duration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'total_credits_required' => ['required', 'integer', 'min:1', 'max:1000'],
            'minimum_residency_credits' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            'qualification_framework_code' => ['sometimes', 'nullable', 'string', 'max:50'],
            'accreditation_body' => ['sometimes', 'nullable', 'string', 'max:100'],
            'accreditation_reference' => ['sometimes', 'nullable', 'string', 'max:100'],
            'accreditation_expires_on' => ['sometimes', 'nullable', 'date'],
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

    private function assertTenant(mixed $institutionId, User $user): void
    {
        abort_unless(is_string($institutionId) && $institutionId === $user->institution_id, 404);
    }

    private function assertMutableVersion(CurriculumVersion $version, User $user): void
    {
        $this->assertTenant($version->institution_id, $user);
        if ($version->isLocked()) {
            abort(response()->json(['error' => ['code' => 'ERR-CUR-002', 'message' => 'Approved curriculum versions are read-only. Create a new version.']], 409));
        }
    }

    /** @return list<string> */
    private function versionRelations(): array
    {
        return ['effectiveYear', 'curriculumCourses.course', 'curriculumCourses.electiveGroup', 'electiveGroups', 'requirements.course', 'requirements.prerequisiteCourse', 'reviewSteps.reviewer'];
    }
}
