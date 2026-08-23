<?php

declare(strict_types=1);

namespace App\Modules\Admission\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admission\Models\Application;
use App\Modules\Admission\Models\ApplicationDocument;
use App\Modules\Admission\Models\ApplicationPayment;
use App\Modules\Admission\Models\Prospect;
use App\Modules\Admission\Services\AdmissionsReportService;
use App\Modules\Admission\Services\AdmissionsWorkflowService;
use App\Modules\Admission\Services\KuccpsImportService;
use App\Modules\Admission\Services\QualificationScoringService;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\RoleAssignment;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Intake;
use App\Modules\Institution\Models\StudyMode;
use App\Modules\Student\Models\Person;
use App\Modules\Student\Models\PersonIdentity;
use App\Platform\Support\Scope;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class AdmissionsController extends Controller
{
    public function __construct(
        private readonly AdmissionsWorkflowService $workflow,
        private readonly AdmissionsReportService $reports,
        private readonly KuccpsImportService $kuccps,
        private readonly QualificationScoringService $scoring,
    ) {}

    public function catalogue(): JsonResponse
    {
        $institution = Institution::query()->where('code', 'MEMA')->firstOrFail();

        return response()->json([
            'data' => [
                'institution' => ['id' => $institution->id, 'name' => $institution->name, 'code' => $institution->code],
                'programmes' => Programme::query()->where('institution_id', $institution->id)->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'award_level', 'duration_years', 'department_id']),
                'campuses' => Campus::query()->where('institution_id', $institution->id)->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'town', 'is_main_campus']),
                'intakes' => Intake::query()->where('institution_id', $institution->id)->where('status', 'ACTIVE')->orderBy('opens_on')->get(),
                'study_modes' => StudyMode::query()->where('institution_id', $institution->id)->where('is_active', true)->orderBy('name')->get(),
                'application_fee' => ['amount' => 1500, 'currency' => 'KES'],
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'given_name' => ['required', 'string', 'max:100'],
            'family_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'national_id' => ['required', 'string', 'max:32'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:32'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:64'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $institution = Institution::query()->where('code', 'MEMA')->firstOrFail();
        $email = mb_strtolower(trim($validated['email']));
        if (User::query()->whereRaw('lower(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages(['email' => ['An account with this email already exists. Please sign in.']]);
        }

        $result = DB::transaction(function () use ($validated, $institution, $email): array {
            $person = Person::query()->create([
                'institution_id' => $institution->id,
                'given_name' => $validated['given_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'family_name' => $validated['family_name'],
                'primary_email' => $email,
                'primary_phone' => $validated['phone'],
                'national_id' => $validated['national_id'],
                'gender' => $validated['gender'] ?? null,
                'nationality' => $validated['nationality'] ?? 'KE',
            ]);

            PersonIdentity::query()->create([
                'institution_id' => $institution->id,
                'person_id' => $person->id,
                'identity_type' => PersonIdentity::TYPE_APPLICANT,
                'identifier' => $validated['national_id'],
                'status' => PersonIdentity::STATUS_ACTIVE,
                'started_on' => now()->toDateString(),
            ]);

            $user = User::query()->create([
                'institution_id' => $institution->id,
                'person_id' => $person->id,
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'is_active' => true,
                'must_change_password' => false,
            ]);

            $role = Role::query()->where('institution_id', $institution->id)->where('code', 'applicant')->firstOrFail();
            RoleAssignment::query()->create([
                'institution_id' => $institution->id,
                'user_id' => $user->id,
                'role_id' => $role->id,
                'scope_type' => Scope::SELF,
                'scope_id' => null,
                'grant_reason' => 'Applicant portal self-registration',
                'starts_at' => now(),
            ]);

            Prospect::query()
                ->where('institution_id', $institution->id)
                ->whereRaw('lower(email) = ?', [$email])
                ->whereNull('converted_at')
                ->update(['status' => 'CONVERTED', 'converted_at' => now()]);

            return [
                'user' => $user,
                'token' => $user->createToken('applicant-portal')->plainTextToken,
                'person' => $person,
            ];
        });

        return response()->json([
            'message' => 'Applicant account created.',
            'data' => [
                'token' => $result['token'],
                'user' => [
                    'id' => $result['user']->id,
                    'email' => $result['user']->email,
                    'person_id' => $result['person']->id,
                ],
            ],
        ], 201);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.application.view');
        $base = Application::query()->where('institution_id', $user->institution_id);

        return response()->json([
            'data' => [
                'total' => (clone $base)->count(),
                'draft' => (clone $base)->where('status', 'DRAFT')->count(),
                'submitted' => (clone $base)->where('status', 'SUBMITTED')->count(),
                'under_review' => (clone $base)->where('status', 'UNDER_REVIEW')->count(),
                'shortlisted' => (clone $base)->where('status', 'SHORTLISTED')->count(),
                'admitted' => (clone $base)->where('status', 'ADMITTED')->count(),
                'accepted' => (clone $base)->where('status', 'ACCEPTED')->count(),
                'rejected' => (clone $base)->where('status', 'REJECTED')->count(),
                'fee_paid' => (clone $base)->where('is_fee_paid', true)->count(),
                'prospects' => Prospect::query()->where('institution_id', $user->institution_id)->where('status', 'NEW')->count(),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.application.view');
        $search = trim($request->string('search')->value());
        $query = Application::query()
            ->where('institution_id', $user->institution_id)
            ->with($this->relations())
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $nested) => $nested
                ->where('application_number', 'ilike', "%{$search}%")
                ->orWhere('kcse_index_number', 'ilike', "%{$search}%")
                ->orWhereHas('person', fn (Builder $person) => $person
                    ->where('given_name', 'ilike', "%{$search}%")
                    ->orWhere('family_name', 'ilike', "%{$search}%")
                    ->orWhere('primary_email', 'ilike', "%{$search}%")
                    ->orWhere('national_id', 'ilike', "%{$search}%")
                    ->orWhere('primary_phone', 'ilike', "%{$search}%"))))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')->value()))
            ->when($request->filled('programme_id'), fn (Builder $q) => $q->where('programme_id', $request->string('programme_id')->value()))
            ->when($request->filled('campus_id'), fn (Builder $q) => $q->where('campus_id', $request->string('campus_id')->value()))
            ->when($request->filled('intake_id'), fn (Builder $q) => $q->where('intake_id', $request->string('intake_id')->value()))
            ->when($request->has('fee_paid'), fn (Builder $q) => $q->where('is_fee_paid', $request->boolean('fee_paid')))
            ->orderByDesc('created_at');

        if ($this->isSelfScopedApplicant($user)) {
            $query->where('person_id', $user->person_id);
        }

        return response()->json(['data' => $query->limit(200)->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, ['admission.application.submit', 'admission.application.review', 'admission.application.decide']);
        $validated = $request->validate([
            'programme_id' => ['required', 'uuid', Rule::exists(Programme::class, 'id')->where('institution_id', $user->institution_id)],
            'campus_id' => ['required', 'uuid', Rule::exists(Campus::class, 'id')->where('institution_id', $user->institution_id)],
            'intake_id' => ['sometimes', 'nullable', 'uuid', Rule::exists(Intake::class, 'id')->where('institution_id', $user->institution_id)],
            'study_mode_id' => ['sometimes', 'nullable', 'uuid', Rule::exists(StudyMode::class, 'id')->where('institution_id', $user->institution_id)],
            'secondary_school_name' => ['required', 'string', 'max:255'],
            'mean_grade' => ['required', 'string', 'max:8'],
            'kcse_index_number' => ['required', 'string', 'max:32'],
            'entry_path' => ['sometimes', Rule::in(['DIRECT', 'KUCCPS', 'TRANSFER', 'SPECIAL'])],
        ]);

        $personId = $user->person_id;
        if ($request->filled('person_id') && ! $this->isSelfScopedApplicant($user)) {
            $this->requirePermission($user, 'admission.application.review');
            $personId = $request->string('person_id')->value();
        }
        abort_unless(is_string($personId), 422, 'A person profile is required.');

        $year = AcademicYear::query()->where('institution_id', $user->institution_id)->where('is_current', true)->firstOrFail();
        $score = $this->scoring->scoreFromMeanGrade($validated['mean_grade']);
        $application = Application::query()->create([
            ...$validated,
            'institution_id' => $user->institution_id,
            'person_id' => $personId,
            'academic_year_id' => $year->id,
            'application_number' => $this->nextApplicationNumber($user->institution_id),
            'status' => 'DRAFT',
            'is_fee_paid' => false,
            'qualification_score' => $score,
            'entry_path' => $validated['entry_path'] ?? 'DIRECT',
            'application_fee_amount' => 1500,
            'application_fee_currency' => 'KES',
        ]);

        return response()->json([
            'message' => 'Application draft created.',
            'data' => $application->load($this->relations()),
        ], 201);
    }

    public function show(Request $request, Application $application): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.application.view');
        $this->assertTenant($application->institution_id, $user);
        $this->assertCanAccessApplication($user, $application);

        return response()->json(['data' => $application->load($this->relations())]);
    }

    public function status(Request $request, Application $application): JsonResponse
    {
        return $this->show($request, $application);
    }

    public function uploadDocument(Request $request, Application $application): JsonResponse
    {
        $user = $this->actor($request);
        $this->assertTenant($application->institution_id, $user);
        $this->assertCanAccessApplication($user, $application);
        if ($application->status !== 'DRAFT' && ! $user->scopesFor('admission.application.review')) {
            throw ValidationException::withMessages(['status' => ['Documents can only be uploaded while the application is a draft.']]);
        }

        $validated = $request->validate([
            'document_type' => ['required', Rule::in(['KCSE_CERTIFICATE', 'NATIONAL_ID', 'PASSPORT_PHOTO', 'BIRTH_CERTIFICATE', 'OTHER'])],
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $file = $validated['file'];
        $path = $file->store("admissions/{$application->id}", 'local');
        $document = ApplicationDocument::query()->create([
            'institution_id' => $application->institution_id,
            'application_id' => $application->id,
            'document_type' => $validated['document_type'],
            'original_name' => $file->getClientOriginalName(),
            'disk_path' => $path,
            'mime_type' => (string) $file->getMimeType(),
            'byte_size' => (int) $file->getSize(),
            'verification_status' => 'PENDING',
        ]);

        return response()->json(['message' => 'Document uploaded.', 'data' => $document], 201);
    }

    public function pay(Request $request, Application $application): JsonResponse
    {
        $user = $this->actor($request);
        $this->assertTenant($application->institution_id, $user);
        $this->assertCanAccessApplication($user, $application);
        $validated = $request->validate([
            'channel' => ['required', Rule::in(['MPESA', 'CARD', 'BANK'])],
            'phone' => ['required', 'string', 'max:32'],
            'transaction_reference' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $payment = $this->workflow->recordPayment(
            $application,
            $validated['channel'],
            $validated['phone'],
            $validated['transaction_reference'] ?? null,
        );

        return response()->json([
            'message' => 'Application fee payment reconciled.',
            'data' => [
                'payment' => $payment,
                'application' => $application->fresh($this->relations()),
            ],
        ]);
    }

    public function submit(Request $request, Application $application): JsonResponse
    {
        $user = $this->actor($request);
        $this->assertTenant($application->institution_id, $user);
        $this->assertCanAccessApplication($user, $application);

        return response()->json([
            'message' => 'Application submitted for admissions review.',
            'data' => $this->workflow->submit($application),
        ]);
    }

    public function beginReview(Request $request, Application $application): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.application.review');
        $this->assertTenant($application->institution_id, $user);

        return response()->json([
            'message' => 'Application is under document screening.',
            'data' => $this->workflow->beginReview($application),
        ]);
    }

    public function verify(Request $request, Application $application): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.application.review');
        $this->assertTenant($application->institution_id, $user);
        $validated = $request->validate(['notes' => ['sometimes', 'nullable', 'string', 'max:2000']]);

        return response()->json([
            'message' => 'Document verification recorded.',
            'data' => $this->workflow->verifyDocuments($application, $user, $validated['notes'] ?? null),
        ]);
    }

    public function decide(Request $request, Application $application): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.application.decide');
        $this->assertTenant($application->institution_id, $user);
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['ADMIT', 'REJECT'])],
            'reference' => ['required', 'string', 'min:3', 'max:128'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $updated = $this->workflow->decide(
            $application,
            $user,
            $validated['decision'],
            $validated['reference'],
            $validated['notes'] ?? null,
        );

        return response()->json([
            'message' => $validated['decision'] === 'ADMIT' ? 'Admission offer issued.' : 'Application rejected.',
            'data' => $updated,
        ]);
    }

    public function acceptOffer(Request $request, Application $application): JsonResponse
    {
        $user = $this->actor($request);
        $this->assertTenant($application->institution_id, $user);
        $this->assertCanAccessApplication($user, $application);

        return response()->json([
            'message' => 'Admission offer accepted. Ready for matriculation.',
            'data' => $this->workflow->acceptOffer($application),
        ]);
    }

    public function offerLetter(Request $request, Application $application): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.application.view');
        $this->assertTenant($application->institution_id, $user);
        $this->assertCanAccessApplication($user, $application);
        abort_unless(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'MATRICULATED'], true), 404);
        $application->load(['person', 'programme', 'campus', 'intake', 'institution']);

        return $this->reports->offerLetter($application);
    }

    public function importKuccps(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.kuccps.import');
        $validated = $request->validate([
            'intake_id' => ['sometimes', 'nullable', 'uuid', Rule::exists(Intake::class, 'id')->where('institution_id', $user->institution_id)],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.kuccps_index' => ['required', 'string', 'max:32'],
            'rows.*.applicant_name' => ['required', 'string', 'max:255'],
            'rows.*.programme_code' => ['required', 'string', 'max:32'],
            'rows.*.mean_grade' => ['sometimes', 'nullable', 'string', 'max:8'],
            'rows.*.aggregate_points' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999'],
        ]);

        $result = $this->kuccps->import($user->institution_id, $validated['intake_id'] ?? null, $validated['rows']);

        return response()->json(['message' => 'KUCCPS placements imported.', 'data' => $result], 201);
    }

    public function prospects(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.prospect.manage');

        return response()->json([
            'data' => Prospect::query()
                ->where('institution_id', $user->institution_id)
                ->with('programmeInterest')
                ->orderByDesc('created_at')
                ->limit(200)
                ->get(),
        ]);
    }

    public function storeProspect(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.prospect.manage');
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'source' => ['sometimes', 'string', 'max:64'],
            'campaign_code' => ['sometimes', 'nullable', 'string', 'max:64'],
            'programme_interest_id' => ['sometimes', 'nullable', 'uuid', Rule::exists(Programme::class, 'id')->where('institution_id', $user->institution_id)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $prospect = Prospect::query()->create($validated + [
            'institution_id' => $user->institution_id,
            'status' => 'NEW',
            'source' => $validated['source'] ?? 'WEBSITE',
        ]);

        return response()->json(['message' => 'Prospect captured.', 'data' => $prospect], 201);
    }

    public function report(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'admission.application.view');
        $format = $request->string('format', 'pdf')->value();
        abort_unless(in_array($format, ['pdf', 'csv'], true), 422);
        $applications = Application::query()
            ->where('institution_id', $user->institution_id)
            ->with(['person', 'programme', 'campus', 'intake'])
            ->orderBy('application_number')
            ->get();

        return $this->reports->intakeRoll($applications, $format);
    }

    public function feeReport(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, ['admission.application.view', 'finance.payment.view']);
        $format = $request->string('format', 'csv')->value();
        abort_unless(in_array($format, ['pdf', 'csv'], true), 422);
        $payments = ApplicationPayment::query()
            ->where('institution_id', $user->institution_id)
            ->where('status', 'COMPLETED')
            ->with('application')
            ->orderByDesc('paid_at')
            ->get();

        return $this->reports->feeRevenue($payments, $format);
    }

    private function nextApplicationNumber(string $institutionId): string
    {
        $year = now()->format('Y');
        $seq = Application::query()->where('institution_id', $institutionId)->withTrashed()->count() + 1;

        return sprintf('APP-%s-%05d', $year, $seq).'-'.strtoupper(Str::random(2));
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

    private function assertCanAccessApplication(User $user, Application $application): void
    {
        if ($this->isSelfScopedApplicant($user) && $application->person_id !== $user->person_id) {
            abort(404);
        }
    }

    private function isSelfScopedApplicant(User $user): bool
    {
        if ($user->scopesFor('admission.application.review') !== [] || $user->scopesFor('admission.application.decide') !== []) {
            return false;
        }

        $scopes = $user->scopesFor('admission.application.view');
        if ($scopes === []) {
            return false;
        }

        return collect($scopes)->every(fn (Scope $scope): bool => $scope->isSelf());
    }

    /** @return list<string> */
    private function relations(): array
    {
        return [
            'person', 'programme.department', 'campus', 'academicYear', 'intake', 'studyMode',
            'documents', 'payments', 'reviews.reviewer',
        ];
    }
}
