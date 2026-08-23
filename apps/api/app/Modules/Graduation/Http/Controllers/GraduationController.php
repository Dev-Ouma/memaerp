<?php

declare(strict_types=1);

namespace App\Modules\Graduation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Graduation\Models\Certificate;
use App\Modules\Graduation\Models\GraduationApplication;
use App\Modules\Graduation\Models\GraduationClearanceCheckpoint;
use App\Modules\Graduation\Services\DegreeAuditService;
use App\Modules\Graduation\Services\GraduationService;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class GraduationController extends Controller
{
    public function __construct(
        private readonly GraduationService $graduation,
        private readonly DegreeAuditService $audit,
    ) {}

    public function apply(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'graduation.clearance.view');
        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();

        return response()->json(['data' => $this->graduation->apply($student)], 201);
    }

    public function clearanceStatus(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'graduation.clearance.view');
        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();

        $application = GraduationApplication::query()
            ->where('student_id', $student->id)
            ->with('checkpoints.clearedBy')
            ->latest('applied_at')
            ->first();

        return response()->json([
            'data' => [
                'audit' => $this->audit->audit($student),
                'application' => $application,
                'finance_clearance' => app(\App\Modules\Finance\Services\ClearanceService::class)->forStudent($student),
            ],
        ]);
    }

    public function clearanceQueue(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'graduation.clearance.clear');

        $checkpoints = GraduationClearanceCheckpoint::query()
            ->where('institution_id', $user->institution_id)
            ->where('status', 'PENDING')
            ->with(['application.student.person', 'application.student.programme', 'clearedBy'])
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        return response()->json(['data' => $checkpoints]);
    }

    public function clearCheckpoint(Request $request, string $checkpointId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'graduation.clearance.clear');

        $checkpoint = GraduationClearanceCheckpoint::query()
            ->where('institution_id', $user->institution_id)
            ->findOrFail($checkpointId);

        $validated = $request->validate(['notes' => ['sometimes', 'nullable', 'string', 'max:500']]);

        return response()->json(['data' => $this->graduation->clearCheckpoint($user, $checkpoint, $validated['notes'] ?? null)]);
    }

    public function transcript(Request $request): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'graduation.transcript.issue');
        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();

        return $this->graduation->transcriptPdf($student);
    }

    public function certificate(Request $request, string $certificateId): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'graduation.transcript.issue');
        $certificate = Certificate::query()->where('institution_id', $user->institution_id)->findOrFail($certificateId);

        return $this->graduation->certificatePdf($certificate);
    }

    public function verifyCertificate(string $token): JsonResponse
    {
        $certificate = $this->graduation->verifyCertificate($token);
        if ($certificate === null) {
            return response()->json(['valid' => false], 404);
        }

        return response()->json([
            'valid' => true,
            'data' => [
                'certificate_number' => $certificate->certificate_number,
                'student_number' => $certificate->student?->student_number,
                'full_name' => $certificate->student?->person?->full_name,
                'programme' => $certificate->student?->programme?->name,
                'issued_at' => $certificate->issued_at?->toISOString(),
            ],
        ]);
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
}
