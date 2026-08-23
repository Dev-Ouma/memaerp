<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attachment\Services\AttachmentAssessmentService;
use App\Modules\Attachment\Services\AttachmentLogbookService;
use App\Modules\Attachment\Services\AttachmentReportService;
use App\Modules\Attachment\Services\AttachmentWorkflowService;
use App\Modules\Iam\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AttachmentController extends Controller
{
    public function __construct(
        private readonly AttachmentWorkflowService $workflow,
        private readonly AttachmentLogbookService $logbook,
        private readonly AttachmentAssessmentService $assessments,
        private readonly AttachmentReportService $reports,
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.report.view');

        return response()->json(['data' => $this->reports->dashboard((string) $user->institution_id)]);
    }

    public function organizations(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, ['attachment.organization.view', 'attachment.application.submit']);

        $activeOnly = ! $user->scopesFor('attachment.organization.manage');

        return response()->json([
            'data' => $this->workflow->listOrganizations((string) $user->institution_id, $activeOnly),
        ]);
    }

    public function storeOrganization(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.organization.manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'industry' => ['sometimes', 'nullable', 'string', 'max:80'],
            'contact_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'contact_email' => ['sometimes', 'nullable', 'email', 'max:120'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'capacity_per_intake' => ['sometimes', 'integer', 'min:1', 'max:500'],
            'mou_valid_until' => ['sometimes', 'nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'quality_rating' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:5'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $org = $this->workflow->createOrganization($user, $validated);

        return response()->json(['data' => $org], 201);
    }

    public function myStatus(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.application.view-self');

        return response()->json(['data' => $this->workflow->myAttachmentStatus($user)]);
    }

    public function applications(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.application.review');

        return response()->json([
            'data' => $this->workflow->listApplications(
                (string) $user->institution_id,
                $request->query('status'),
            ),
        ]);
    }

    public function submitApplication(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.application.submit');

        $validated = $request->validate([
            'term_id' => ['sometimes', 'nullable', 'uuid'],
            'preferred_organization_ids' => ['sometimes', 'array'],
            'preferred_organization_ids.*' => ['uuid'],
            'motivation' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        $application = $this->workflow->submitApplication($user, $validated);

        return response()->json(['data' => $application], 201);
    }

    public function reviewApplication(Request $request, string $applicationId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.application.review');

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:APPROVE,REJECT'],
            'review_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'rejection_reason' => ['required_if:decision,REJECT', 'nullable', 'string', 'max:2000'],
        ]);

        $application = $this->workflow->reviewApplication(
            $user,
            $applicationId,
            $validated['decision'],
            $validated['review_notes'] ?? null,
            $validated['rejection_reason'] ?? null,
        );

        return response()->json(['data' => $application]);
    }

    public function placements(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.placement.manage');

        return response()->json([
            'data' => $this->workflow->allPlacements((string) $user->institution_id),
        ]);
    }

    public function mySupervisedPlacements(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.placement.supervise');

        return response()->json(['data' => $this->workflow->supervisedPlacements($user)]);
    }

    public function createPlacement(Request $request, string $applicationId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.placement.manage');

        $validated = $request->validate([
            'host_organization_id' => ['required', 'uuid'],
            'university_supervisor_id' => ['sometimes', 'nullable', 'uuid'],
            'field_supervisor_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'field_supervisor_email' => ['sometimes', 'nullable', 'email', 'max:120'],
            'field_supervisor_phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
        ]);

        $placement = $this->workflow->createPlacement($user, $applicationId, $validated);

        return response()->json(['data' => $placement], 201);
    }

    public function confirmHost(Request $request, string $placementId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.placement.manage');

        $placement = $this->workflow->confirmHostAcceptance($user, $placementId);

        return response()->json(['data' => $placement]);
    }

    public function logbookEntries(Request $request, string $placementId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, [
            'attachment.logbook.view',
            'attachment.logbook.view-self',
            'attachment.placement.supervise',
        ]);

        if ($user->scopesFor('attachment.logbook.view-self') !== []
            && $user->scopesFor('attachment.logbook.view') === []
            && $user->scopesFor('attachment.placement.supervise') === []) {
            $this->workflow->findStudentPlacement($user, $placementId);
        } else {
            $this->workflow->findInstitutionPlacement($user, $placementId);
        }

        return response()->json(['data' => $this->logbook->entriesForPlacement($placementId)]);
    }

    public function submitLogbook(Request $request, string $placementId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.logbook.submit');

        $validated = $request->validate([
            'week_number' => ['required', 'integer', 'min:1', 'max:52'],
            'week_start' => ['required', 'date'],
            'activities_summary' => ['required', 'string', 'min:10', 'max:5000'],
            'skills_learned' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'hours_logged' => ['sometimes', 'numeric', 'min:0', 'max:168'],
        ]);

        $entry = $this->logbook->submitEntry($user, $placementId, $validated);

        return response()->json(['data' => $entry], 201);
    }

    public function endorseLogbook(Request $request, string $entryId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'attachment.logbook.endorse');

        $validated = $request->validate([
            'host_comment' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $entry = $this->logbook->endorseEntry($user, $entryId, $validated['host_comment'] ?? null);

        return response()->json(['data' => $entry]);
    }

    public function submitAssessment(Request $request, string $placementId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, ['attachment.assessment.submit', 'attachment.assessment.manage']);

        $validated = $request->validate([
            'assessment_type' => ['required', 'string', 'in:HOST_EVAL,UNIVERSITY_EVAL,FINAL_REPORT'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_score' => ['sometimes', 'numeric', 'min:1', 'max:100'],
            'comments' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'assessor_name' => ['sometimes', 'nullable', 'string', 'max:120'],
        ]);

        if ($validated['assessment_type'] === 'HOST_EVAL') {
            $this->requirePermission($user, 'attachment.assessment.manage');
            $assessment = $this->assessments->recordHostEvaluation($user, $placementId, $validated);
        } else {
            $this->requirePermission($user, 'attachment.assessment.submit');
            $assessment = $this->assessments->submitAssessment($user, $placementId, $validated);
        }

        return response()->json(['data' => $assessment], 201);
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
}
