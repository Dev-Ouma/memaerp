<?php

declare(strict_types=1);

namespace App\Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\CalendarEvent;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Faculty;
use App\Modules\Institution\Models\Intake;
use App\Modules\Institution\Models\MasterLookup;
use App\Modules\Institution\Models\StudyMode;
use App\Modules\Institution\Models\Term;
use App\Modules\Institution\Models\Unit;
use App\Modules\Institution\Notifications\TermActivatedNotification;
use App\Modules\Institution\Services\AcademicCalendarService;
use App\Modules\Institution\Services\InstitutionReportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class InstitutionAdminController extends Controller
{
    public function __construct(
        private readonly AcademicCalendarService $calendar,
        private readonly InstitutionReportService $reports,
    ) {}

    public function overview(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, ['institution.structure.view', 'institution.calendar.view']);
        $id = $user->institution_id;

        return response()->json(['data' => [
            'campuses' => Campus::query()->where('institution_id', $id)->where('is_active', true)->count(),
            'faculties' => Faculty::query()->where('institution_id', $id)->where('is_active', true)->count(),
            'departments' => Department::query()->where('institution_id', $id)->where('is_active', true)->count(),
            'units' => Unit::query()->where('institution_id', $id)->where('is_active', true)->count(),
            'intakes' => Intake::query()->where('institution_id', $id)->where('status', 'ACTIVE')->count(),
            'current_academic_year' => AcademicYear::query()->where('institution_id', $id)->current()->with('terms')->first(),
        ]]);
    }

    public function campuses(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.view');

        $query = Campus::query()->where('institution_id', $user->institution_id)->withCount('faculties');
        $this->applySearchAndStatus($request, $query);
        $page = $query->orderBy('name')->paginate($this->perPage($request));

        return response()->json(['data' => $page->items(), 'meta' => $this->pageMeta($page)]);
    }

    public function storeCampus(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32', Rule::unique(Campus::class, 'code')->where('institution_id', $user->institution_id)],
            'name' => ['required', 'string', 'max:200'],
            'town' => ['nullable', 'string', 'max:120'],
            'address' => ['sometimes', 'array'],
            'head_of_unit_id' => ['sometimes', 'nullable', 'uuid'],
            'is_main_campus' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'ARCHIVED'])],
            'resolution_reference' => ['required_if:status,ACTIVE', 'nullable', 'string', 'max:128'],
        ]);
        $campus = Campus::query()->create($validated + ['institution_id' => $user->institution_id, 'is_active' => ($validated['status'] ?? 'DRAFT') === 'ACTIVE']);

        return response()->json(['message' => 'Campus created.', 'data' => $campus], 201);
    }

    public function updateCampus(Request $request, Campus $campus): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $this->assertTenant($campus->institution_id, $user);
        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:32', Rule::unique(Campus::class, 'code')->ignore($campus->id)->where('institution_id', $user->institution_id)],
            'name' => ['sometimes', 'string', 'max:200'],
            'town' => ['sometimes', 'nullable', 'string', 'max:120'],
            'head_of_unit_id' => ['sometimes', 'nullable', 'uuid'],
            'is_main_campus' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'ARCHIVED'])],
            'resolution_reference' => ['required_if:status,ACTIVE', 'sometimes', 'nullable', 'string', 'max:128'],
        ]);
        $campus->auditReason('Institutional campus record updated')->update($this->withActiveState($validated));

        return response()->json(['message' => 'Campus updated.', 'data' => $campus->fresh()]);
    }

    public function faculties(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.view');
        $query = Faculty::query()->where('institution_id', $user->institution_id)->with(['campus'])->withCount('departments');
        $this->applySearchAndStatus($request, $query);
        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->string('campus_id')->value());
        }
        $page = $query->orderBy('name')->paginate($this->perPage($request));

        return response()->json(['data' => $page->items(), 'meta' => $this->pageMeta($page)]);
    }

    public function storeFaculty(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $validated = $request->validate([
            'campus_id' => ['required', 'uuid', Rule::exists(Campus::class, 'id')->where('institution_id', $user->institution_id)],
            'code' => ['required', 'string', 'max:32', Rule::unique(Faculty::class, 'code')->where('institution_id', $user->institution_id)],
            'name' => ['required', 'string', 'max:200'],
            'type' => ['sometimes', Rule::in(['FACULTY', 'SCHOOL', 'CENTRE'])],
            'head_of_unit_id' => ['sometimes', 'nullable', 'uuid'],
            'status' => ['required', Rule::in(['DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'ARCHIVED'])],
            'resolution_reference' => ['required_if:status,ACTIVE', 'nullable', 'string', 'max:128'],
        ]);
        $faculty = Faculty::query()->create($validated + ['institution_id' => $user->institution_id, 'is_active' => $validated['status'] === 'ACTIVE']);

        return response()->json(['message' => 'Faculty created.', 'data' => $faculty->load('campus')], 201);
    }

    public function updateFaculty(Request $request, Faculty $faculty): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $this->assertTenant($faculty->institution_id, $user);
        $validated = $request->validate([
            'campus_id' => ['sometimes', 'uuid', Rule::exists(Campus::class, 'id')->where('institution_id', $user->institution_id)],
            'code' => ['sometimes', 'string', 'max:32', Rule::unique(Faculty::class, 'code')->ignore($faculty->id)->where('institution_id', $user->institution_id)],
            'name' => ['sometimes', 'string', 'max:200'],
            'type' => ['sometimes', Rule::in(['FACULTY', 'SCHOOL', 'CENTRE'])],
            'head_of_unit_id' => ['sometimes', 'nullable', 'uuid'],
            'status' => ['sometimes', Rule::in(['DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'ARCHIVED'])],
            'resolution_reference' => ['required_if:status,ACTIVE', 'sometimes', 'nullable', 'string', 'max:128'],
        ]);
        $faculty->auditReason('Institutional faculty or school record updated')->update($this->withActiveState($validated));

        return response()->json(['message' => 'Faculty updated.', 'data' => $faculty->fresh('campus')]);
    }

    public function departments(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.view');
        $query = Department::query()->where('institution_id', $user->institution_id)->with('faculty.campus');
        $this->applySearchAndStatus($request, $query);
        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->string('faculty_id')->value());
        }
        $page = $query->orderBy('name')->paginate($this->perPage($request));

        return response()->json(['data' => $page->items(), 'meta' => $this->pageMeta($page)]);
    }

    public function storeDepartment(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $validated = $request->validate([
            'faculty_id' => ['required', 'uuid', Rule::exists(Faculty::class, 'id')->where('institution_id', $user->institution_id)],
            'code' => ['required', 'string', 'max:32', Rule::unique(Department::class, 'code')->where('institution_id', $user->institution_id)],
            'name' => ['required', 'string', 'max:200'],
            'cost_centre' => ['nullable', 'string', 'max:32'],
            'head_of_unit_id' => ['sometimes', 'nullable', 'uuid'],
            'status' => ['required', Rule::in(['DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'ARCHIVED'])],
            'resolution_reference' => ['required_if:status,ACTIVE', 'nullable', 'string', 'max:128'],
        ]);
        $department = Department::query()->create($validated + ['institution_id' => $user->institution_id, 'is_active' => $validated['status'] === 'ACTIVE']);

        return response()->json(['message' => 'Department created.', 'data' => $department->load('faculty.campus')], 201);
    }

    public function updateDepartment(Request $request, Department $department): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $this->assertTenant($department->institution_id, $user);
        $validated = $request->validate([
            'faculty_id' => ['sometimes', 'uuid', Rule::exists(Faculty::class, 'id')->where('institution_id', $user->institution_id)],
            'code' => ['sometimes', 'string', 'max:32', Rule::unique(Department::class, 'code')->ignore($department->id)->where('institution_id', $user->institution_id)],
            'name' => ['sometimes', 'string', 'max:200'],
            'cost_centre' => ['sometimes', 'nullable', 'string', 'max:32'],
            'head_of_unit_id' => ['sometimes', 'nullable', 'uuid'],
            'status' => ['sometimes', Rule::in(['DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'ARCHIVED'])],
            'resolution_reference' => ['required_if:status,ACTIVE', 'sometimes', 'nullable', 'string', 'max:128'],
        ]);
        $department->auditReason('Institutional department record updated')->update($this->withActiveState($validated));

        return response()->json(['message' => 'Department updated.', 'data' => $department->fresh('faculty.campus')]);
    }

    public function units(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.view');
        $query = Unit::query()->where('institution_id', $user->institution_id)->with('department.faculty.campus');
        $this->applySearchAndStatus($request, $query);
        $page = $query->orderBy('name')->paginate($this->perPage($request));

        return response()->json(['data' => $page->items(), 'meta' => $this->pageMeta($page)]);
    }

    public function storeUnit(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $validated = $request->validate([
            'department_id' => ['required', 'uuid', Rule::exists(Department::class, 'id')->where('institution_id', $user->institution_id)],
            'code' => ['required', 'string', 'max:32', Rule::unique(Unit::class, 'code')->where('institution_id', $user->institution_id)],
            'name' => ['required', 'string', 'max:200'],
            'type' => ['sometimes', 'string', 'max:32'],
            'head_of_unit_id' => ['sometimes', 'nullable', 'uuid'],
            'status' => ['required', Rule::in(['DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'ARCHIVED'])],
            'resolution_reference' => ['required_if:status,ACTIVE', 'nullable', 'string', 'max:128'],
        ]);
        $unit = Unit::query()->create($this->withActiveState($validated) + ['institution_id' => $user->institution_id]);

        return response()->json(['message' => 'Unit created.', 'data' => $unit->load('department.faculty.campus')], 201);
    }

    public function updateUnit(Request $request, Unit $unit): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $this->assertTenant($unit->institution_id, $user);
        $validated = $request->validate([
            'department_id' => ['sometimes', 'uuid', Rule::exists(Department::class, 'id')->where('institution_id', $user->institution_id)],
            'code' => ['sometimes', 'string', 'max:32', Rule::unique(Unit::class, 'code')->ignore($unit->id)->where('institution_id', $user->institution_id)],
            'name' => ['sometimes', 'string', 'max:200'],
            'type' => ['sometimes', 'string', 'max:32'],
            'head_of_unit_id' => ['sometimes', 'nullable', 'uuid'],
            'status' => ['sometimes', Rule::in(['DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'ARCHIVED'])],
            'resolution_reference' => ['required_if:status,ACTIVE', 'sometimes', 'nullable', 'string', 'max:128'],
        ]);
        $unit->auditReason('Institutional unit record updated')->update($this->withActiveState($validated));

        return response()->json(['message' => 'Unit updated.', 'data' => $unit->fresh('department.faculty.campus')]);
    }

    public function academicYears(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.view');
        $page = AcademicYear::query()->where('institution_id', $user->institution_id)->with('terms')
            ->orderByDesc('starts_on')->paginate($this->perPage($request));

        return response()->json(['data' => $page->items(), 'meta' => $this->pageMeta($page)]);
    }

    public function currentAcademicYear(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.view');
        $year = AcademicYear::query()->where('institution_id', $user->institution_id)->current()->with(['terms' => fn ($q) => $q->orderBy('sequence')])->firstOrFail();

        return response()->json(['data' => $year]);
    }

    public function storeAcademicYear(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.manage');
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:16', Rule::unique(AcademicYear::class, 'code')->where('institution_id', $user->institution_id)],
            'name' => ['required', 'string', 'max:100'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
        ]);
        $year = AcademicYear::query()->create($validated + ['institution_id' => $user->institution_id, 'status' => 'DRAFT', 'is_current' => false]);

        return response()->json(['message' => 'Academic year created in draft.', 'data' => $year], 201);
    }

    public function activateAcademicYear(Request $request, AcademicYear $academicYear): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.manage');
        abort_unless($academicYear->institution_id === $user->institution_id, 404);
        $validated = $request->validate(['senate_resolution_reference' => ['required', 'string', 'min:5', 'max:128']]);
        $year = $this->calendar->activateAcademicYear($academicYear, $validated['senate_resolution_reference']);

        return response()->json(['message' => 'Academic year activated and published.', 'data' => $year]);
    }

    public function storeTerm(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.manage');
        $validated = $request->validate([
            'academic_year_id' => ['required', 'uuid', Rule::exists(AcademicYear::class, 'id')->where('institution_id', $user->institution_id)],
            'study_mode_code' => ['required', 'string', Rule::exists(StudyMode::class, 'code')->where('institution_id', $user->institution_id)->where('is_active', true)],
            'code' => ['required', 'string', 'max:16', Rule::unique(Term::class, 'code')->where('institution_id', $user->institution_id)],
            'name' => ['required', 'string', 'max:100'],
            'sequence' => ['required', 'integer', 'min:1', 'max:6'],
            'term_type' => ['required', Rule::in(['SEMESTER', 'TRIMESTER', 'TERM', 'SESSION'])],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'registration_opens_at' => ['nullable', 'date', 'before:registration_closes_at'],
            'registration_closes_at' => ['nullable', 'date'],
            'add_drop_closes_at' => ['nullable', 'date'],
            'fee_payment_closes_at' => ['nullable', 'date'],
            'marks_entry_opens_at' => ['nullable', 'date', 'before:marks_entry_closes_at'],
            'marks_entry_closes_at' => ['nullable', 'date'],
            'exam_starts_on' => ['nullable', 'date'],
            'exam_ends_on' => ['nullable', 'date', 'after_or_equal:exam_starts_on'],
        ]);

        $term = Term::query()->create($validated + ['institution_id' => $user->institution_id, 'status' => 'DRAFT', 'is_current' => false]);

        return response()->json(['message' => 'Academic term created in draft.', 'data' => $term], 201);
    }

    public function activateTerm(Request $request, Term $term): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.manage');
        abort_unless($term->institution_id === $user->institution_id, 404);
        $activated = $this->calendar->activateTerm($term);
        $recipients = User::query()->where('institution_id', $user->institution_id)->active()->get();
        Notification::send($recipients, new TermActivatedNotification($activated));

        return response()->json(['message' => 'Academic term activated and broadcast queued.', 'data' => $activated]);
    }

    public function studyModes(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.view');
        $modes = StudyMode::query()->where('institution_id', $user->institution_id)->orderBy('name')->get();

        return response()->json(['data' => $modes]);
    }

    public function storeStudyMode(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.manage');
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32', Rule::unique(StudyMode::class, 'code')->where('institution_id', $user->institution_id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);
        $mode = StudyMode::query()->create($validated + ['institution_id' => $user->institution_id, 'is_active' => true]);

        return response()->json(['message' => 'Study mode created.', 'data' => $mode], 201);
    }

    public function updateStudyMode(Request $request, StudyMode $studyMode): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.manage');
        $this->assertTenant($studyMode->institution_id, $user);
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $studyMode->auditReason('Study mode configuration updated')->update($validated);

        return response()->json(['message' => 'Study mode updated.', 'data' => $studyMode->fresh()]);
    }

    public function intakes(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.view');
        $query = Intake::query()->where('institution_id', $user->institution_id)->with('academicYear');
        $query->when($request->filled('academic_year_id'), fn ($q) => $q->where('academic_year_id', $request->string('academic_year_id')->value()));
        $page = $query->orderByDesc('opens_on')->paginate($this->perPage($request));

        return response()->json(['data' => $page->items(), 'meta' => $this->pageMeta($page)]);
    }

    public function storeIntake(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.manage');
        $validated = $request->validate([
            'academic_year_id' => ['required', 'uuid', Rule::exists(AcademicYear::class, 'id')->where('institution_id', $user->institution_id)],
            'code' => ['required', 'string', 'max:32', Rule::unique(Intake::class, 'code')->where('institution_id', $user->institution_id)],
            'name' => ['required', 'string', 'max:100'],
            'opens_on' => ['required', 'date'],
            'closes_on' => ['required', 'date', 'after:opens_on'],
            'reporting_on' => ['nullable', 'date', 'after_or_equal:closes_on'],
            'status' => ['sometimes', Rule::in(['DRAFT', 'ACTIVE', 'ARCHIVED'])],
        ]);
        $intake = Intake::query()->create($validated + ['institution_id' => $user->institution_id]);

        return response()->json(['message' => 'Intake created.', 'data' => $intake->load('academicYear')], 201);
    }

    public function updateIntake(Request $request, Intake $intake): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.manage');
        $this->assertTenant($intake->institution_id, $user);
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'opens_on' => ['sometimes', 'date'],
            'closes_on' => ['sometimes', 'date', 'after:opens_on'],
            'reporting_on' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in(['DRAFT', 'ACTIVE', 'ARCHIVED'])],
        ]);
        $intake->auditReason('Admissions intake window updated')->update($validated);

        return response()->json(['message' => 'Intake updated.', 'data' => $intake->fresh('academicYear')]);
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.view');
        $events = CalendarEvent::query()->where('institution_id', $user->institution_id)
            ->when($request->filled('academic_year_id'), fn ($q) => $q->where('academic_year_id', $request->string('academic_year_id')->value()))
            ->orderBy('starts_at')->get();

        return response()->json(['data' => $events]);
    }

    public function storeCalendarEvent(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.manage');
        $validated = $request->validate([
            'academic_year_id' => ['nullable', 'uuid', Rule::exists(AcademicYear::class, 'id')->where('institution_id', $user->institution_id)],
            'term_id' => ['nullable', 'uuid', Rule::exists(Term::class, 'id')->where('institution_id', $user->institution_id)],
            'event_type' => ['required', Rule::in(['HOLIDAY', 'REGISTRATION', 'EXAM', 'DEADLINE', 'LECTURE', 'CEREMONY', 'OTHER'])],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_critical' => ['sometimes', 'boolean'],
            'is_holiday' => ['sometimes', 'boolean'],
        ]);
        $event = CalendarEvent::query()->create($validated + ['institution_id' => $user->institution_id]);

        return response()->json(['message' => 'Calendar event created.', 'data' => $event], 201);
    }

    public function lookups(Request $request, string $type): JsonResponse
    {
        $user = $this->actor($request);
        $key = "institution:{$user->institution_id}:lookups:".strtolower($type);
        $startedAt = hrtime(true);
        $values = Cache::remember($key, 3600, fn () => MasterLookup::query()
            ->where('institution_id', $user->institution_id)->where('type', strtoupper($type))->where('is_active', true)
            ->orderBy('display_order')->orderBy('name')->get());

        return response()->json(['data' => $values, 'meta' => ['cache_key' => $key, 'elapsed_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 3)]]);
    }

    public function storeLookup(Request $request, string $type): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $normalizedType = strtoupper($type);
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:48', Rule::unique(MasterLookup::class, 'code')->where('institution_id', $user->institution_id)->where('type', $normalizedType)],
            'name' => ['required', 'string', 'max:160'],
            'metadata' => ['sometimes', 'array'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ]);
        $lookup = MasterLookup::query()->create($validated + ['institution_id' => $user->institution_id, 'type' => $normalizedType]);
        Cache::forget("institution:{$user->institution_id}:lookups:".strtolower($type));

        return response()->json(['message' => 'Master lookup value created.', 'data' => $lookup], 201);
    }

    public function updateLookup(Request $request, string $type, MasterLookup $lookup): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.manage');
        $this->assertTenant($lookup->institution_id, $user);
        abort_unless($lookup->type === strtoupper($type), 404);
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:160'],
            'metadata' => ['sometimes', 'array'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'effective_from' => ['sometimes', 'nullable', 'date'],
            'effective_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:effective_from'],
        ]);
        $lookup->auditReason('Master lookup value updated')->update($validated);
        Cache::forget("institution:{$user->institution_id}:lookups:".strtolower($type));

        return response()->json(['message' => 'Master lookup value updated.', 'data' => $lookup->fresh()]);
    }

    public function directoryReport(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.structure.view');
        $validated = $request->validate(['format' => ['sometimes', Rule::in(['pdf', 'csv', 'json'])]]);

        return $this->reports->directory($user->institution_id, $validated['format'] ?? 'pdf');
    }

    public function calendarReport(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'institution.calendar.view');
        $validated = $request->validate([
            'academic_year_id' => ['sometimes', 'uuid', Rule::exists(AcademicYear::class, 'id')->where('institution_id', $user->institution_id)],
        ]);

        return $this->reports->calendar($user->institution_id, $validated['academic_year_id'] ?? null);
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

    /**
     * Status is the workflow source of truth; the legacy active flag is maintained for fast
     * filters and compatibility with modules that pre-date governed master-data states.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withActiveState(array $validated): array
    {
        if (isset($validated['status']) && is_string($validated['status'])) {
            $validated['is_active'] = $validated['status'] === 'ACTIVE';
        }

        return $validated;
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

    /**
     * @template TModel of Campus|Faculty|Department|Unit
     *
     * @param  Builder<TModel>  $query
     */
    private function applySearchAndStatus(Request $request, Builder $query): void
    {
        $search = trim($request->string('search')->value());
        $query->when($search !== '', fn ($q) => $q->where(fn ($nested) => $nested->where('name', 'ilike', "%{$search}%")->orWhere('code', 'ilike', "%{$search}%")));
        $query->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->boolean('active')));
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 25), 1), 100);
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $page
     * @return array{current_page: int, per_page: int, total: int, last_page: int}
     */
    private function pageMeta(LengthAwarePaginator $page): array
    {
        return ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()];
    }
}
