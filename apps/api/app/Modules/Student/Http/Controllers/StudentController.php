<?php

declare(strict_types=1);

namespace App\Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admission\Models\Application;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Http\Requests\ListStudentsRequest;
use App\Modules\Student\Http\Requests\MatriculateStudentsRequest;
use App\Modules\Student\Http\Requests\UpdateStudentRequest;
use App\Modules\Student\Http\Requests\UpdateStudentStatusRequest;
use App\Modules\Student\Http\Resources\StudentResource;
use App\Modules\Student\Services\DigitalIdService;
use App\Modules\Student\Services\MatriculationService;
use App\Modules\Student\Services\StudentRecords;
use App\Modules\Student\Services\StudentReportService;
use App\Modules\Student\Services\StudentStatusService;
use App\Platform\Support\RequestContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class StudentController extends Controller
{
    public function __construct(
        private readonly StudentRecords $records,
        private readonly MatriculationService $matriculation,
        private readonly StudentStatusService $statuses,
        private readonly StudentReportService $reports,
        private readonly DigitalIdService $digitalId,
        private readonly RequestContext $context,
    ) {}

    public function index(ListStudentsRequest $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $validated = $request->validated();
        $paginator = $this->records->paginate(
            $actor,
            $validated['filter'] ?? [],
            $validated['sort'] ?? 'student_number',
            $validated['per_page'] ?? 25,
        );

        return response()->json([
            'data' => StudentResource::collection($paginator->items())->resolve($request),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
                'pagination' => [
                    'next_cursor' => $paginator->nextCursor()?->encode(),
                    'per_page' => $paginator->perPage(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ],
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'student.record.view');

        return response()->json([
            'data' => $this->reports->dashboard((string) $user->institution_id),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function matriculationQueue(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, ['student.record.matriculate', 'student.record.view']);

        $applications = Application::query()
            ->where('institution_id', $user->institution_id)
            ->where('status', 'ACCEPTED')
            ->whereNotNull('documents_verified_at')
            ->whereDoesntHave('matriculationLog')
            ->with(['person', 'programme', 'campus', 'intake', 'academicYear'])
            ->orderBy('offer_accepted_at')
            ->limit(100)
            ->get();

        return response()->json([
            'data' => $applications->map(fn (Application $application): array => [
                'id' => $application->id,
                'application_number' => $application->application_number,
                'status' => $application->status,
                'person' => [
                    'full_name' => $application->person?->full_name,
                    'primary_email' => $application->person?->primary_email,
                ],
                'programme' => [
                    'code' => $application->programme?->code,
                    'name' => $application->programme?->name,
                ],
                'campus' => ['code' => $application->campus?->code, 'name' => $application->campus?->name],
                'intake' => ['code' => $application->intake?->code],
                'offer_accepted_at' => $application->offer_accepted_at?->toISOString(),
            ]),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function matriculate(MatriculateStudentsRequest $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $validated = $request->validated();

        $students = $this->matriculation->matriculateBatch(
            $actor,
            $validated['application_ids'],
            (bool) ($validated['pledge_signed'] ?? false),
            $validated['notes'] ?? null,
        );

        return response()->json([
            'message' => count($students).' student(s) matriculated successfully.',
            'data' => StudentResource::collection(collect($students))->resolve($request),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ], 201);
    }

    public function show(ListStudentsRequest $request, string $student): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $record = $this->records->findVisible($actor, $student);

        return response()->json([
            'data' => (new StudentResource($record))->resolve($request),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function showByNumber(ListStudentsRequest $request, string $studentNumber): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $record = $this->records->findVisibleByNumber($actor, $studentNumber);

        return response()->json([
            'data' => (new StudentResource($record))->resolve($request),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function update(UpdateStudentRequest $request, string $student): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $validated = $request->validated();
        $reason = $validated['change_reason'];
        unset($validated['change_reason']);

        $record = $this->records->update($actor, $student, $validated, $reason);

        return response()->json([
            'data' => (new StudentResource($record))->resolve($request),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function updateStatus(UpdateStudentStatusRequest $request, string $student): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $validated = $request->validated();
        $record = $this->records->findVisible($actor, $student, 'student.record.status');
        $updated = $this->statuses->updateStatus($actor, $record, $validated['status'], $validated['reason']);

        return response()->json([
            'data' => (new StudentResource($updated))->resolve($request),
            'meta' => [
                'request_id' => $this->context->requestId(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function digitalId(Request $request, string $student): Response
    {
        $user = $this->actor($request);
        $record = $this->records->findVisible($user, $student, 'student.record.view');

        if ($this->isSelfScopedStudent($user) && $record->person_id !== $user->person_id) {
            abort(404);
        }

        return $this->digitalId->renderPdf($record);
    }

    public function verifyId(string $token): JsonResponse
    {
        $student = $this->digitalId->verify($token);
        if ($student === null) {
            return response()->json([
                'valid' => false,
                'message' => 'Digital ID token is invalid or no longer active.',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'data' => [
                'student_number' => $student->student_number,
                'full_name' => $student->person?->full_name,
                'programme' => $student->programme?->name,
                'campus' => $student->campus?->name,
                'status' => $student->status,
                'verified_at' => now()->toISOString(),
            ],
        ]);
    }

    public function report(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'student.record.view');

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:matriculation,directory'],
            'format' => ['sometimes', 'string', 'in:pdf,csv,json'],
            'intake_id' => ['sometimes', 'nullable', 'uuid'],
        ]);

        $format = $validated['format'] ?? 'pdf';
        $institutionId = (string) $user->institution_id;

        return match ($validated['type']) {
            'matriculation' => $this->reports->matriculationRoll(
                $institutionId,
                $validated['intake_id'] ?? null,
                $format,
            ),
            'directory' => $this->reports->masterDirectory($institutionId, $format === 'pdf' ? 'csv' : $format),
        };
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
        $viewScopes = $user->scopesFor('student.record.view');
        if ($viewScopes === []) {
            return false;
        }

        if ($user->scopesFor('student.record.update') !== [] || $user->scopesFor('student.record.matriculate') !== []) {
            return false;
        }

        return collect($viewScopes)->every(fn ($scope) => $scope->isSelf());
    }
}
