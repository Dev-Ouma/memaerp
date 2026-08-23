<?php

declare(strict_types=1);

namespace App\Modules\Advising\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Advising\Services\AdvisingDegreeAuditService;
use App\Modules\Advising\Services\AdvisingService;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdvisingController extends Controller
{
    public function __construct(
        private readonly AdvisingService $advising,
        private readonly AdvisingDegreeAuditService $audit,
    ) {}

    public function myAdvisees(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'advising.advisee.view');

        return response()->json(['data' => $this->advising->myAdvisees($user)]);
    }

    public function assignments(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'advising.assignment.manage');

        return response()->json(['data' => $this->advising->allAssignments((string) $user->institution_id)]);
    }

    public function assign(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'advising.assignment.manage');

        $validated = $request->validate([
            'student_id' => ['required', 'uuid'],
            'advisor_user_id' => ['required', 'uuid'],
            'assignment_reason' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $assignment = $this->advising->assignAdvisor(
            $user,
            $validated['student_id'],
            $validated['advisor_user_id'],
            $validated['assignment_reason'] ?? null,
        );

        return response()->json(['data' => $assignment], 201);
    }

    public function degreeAudit(Request $request, string $studentId): JsonResponse
    {
        $user = $this->actor($request);
        $student = Student::query()
            ->where('institution_id', $user->institution_id)
            ->findOrFail($studentId);

        if ($this->isOwnStudent($user, $student)) {
            $this->requirePermission($user, 'advising.progress.view-self');
        } else {
            $this->requireAnyPermission($user, ['advising.advisee.view', 'advising.assignment.manage']);
        }

        return response()->json(['data' => $this->audit->audit($student)]);
    }

    public function myProgress(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'advising.progress.view-self');
        $student = Student::query()->where('person_id', $user->person_id)->firstOrFail();
        $audit = $this->audit->audit($student);
        $advisor = $this->advising->myAdvisor($student);

        return response()->json([
            'data' => [
                ...$audit,
                'advisor' => $advisor === null ? null : [
                    'id' => $advisor->advisor_user_id,
                    'name' => $advisor->advisor?->person?->full_name,
                    'email' => $advisor->advisor?->email,
                    'assigned_at' => $advisor->assigned_at,
                ],
                'notes' => $this->advising->notesForStudent($user, $student->id, studentView: true),
            ],
        ]);
    }

    public function storeNote(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'advising.notes.manage');

        $validated = $request->validate([
            'student_id' => ['required', 'uuid'],
            'note_text' => ['required', 'string', 'min:3', 'max:5000'],
            'note_type' => ['sometimes', 'string', 'in:GENERAL,RECOMMENDATION,INTERVENTION,FOLLOW_UP'],
            'is_confidential' => ['sometimes', 'boolean'],
            'visible_to_student' => ['sometimes', 'boolean'],
            'follow_up_status' => ['sometimes', 'string', 'in:NONE,PENDING,DONE'],
            'follow_up_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $note = $this->advising->addNote($user, $validated['student_id'], $validated);

        return response()->json(['data' => $note], 201);
    }

    public function studentNotes(Request $request, string $studentId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'advising.notes.manage');

        return response()->json(['data' => $this->advising->notesForStudent($user, $studentId)]);
    }

    public function requestSession(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'advising.session.request');

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'mode' => ['sometimes', 'string', 'in:IN_PERSON,ONLINE,PHONE'],
            'topic' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $session = $this->advising->requestSession($user, $validated);

        return response()->json(['data' => $session], 201);
    }

    public function mySessions(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'advising.advisee.view');

        return response()->json(['data' => $this->advising->sessionsForAdvisor($user)]);
    }

    public function updateSession(Request $request, string $sessionId): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'advising.advisee.view');

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:REQUESTED,CONFIRMED,COMPLETED,CANCELLED'],
            'outcome' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $session = $this->advising->updateSessionStatus(
            $user,
            $sessionId,
            $validated['status'],
            $validated['outcome'] ?? null,
        );

        return response()->json(['data' => $session]);
    }

    private function isOwnStudent(User $user, Student $student): bool
    {
        return (string) $user->person_id === (string) $student->person_id;
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
