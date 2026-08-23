<?php

declare(strict_types=1);

namespace App\Modules\Advising\Services;

use App\Modules\Advising\Models\AdvisorAssignment;
use App\Modules\Advising\Models\AdvisoryNote;
use App\Modules\Advising\Models\AdvisingSession;
use App\Modules\Examination\Models\TermGpa;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class AdvisingService
{
    public function __construct(private readonly AdvisingDegreeAuditService $audit) {}

    public function assignAdvisor(User $actor, string $studentId, string $advisorUserId, ?string $reason = null): AdvisorAssignment
    {
        $student = Student::query()
            ->where('institution_id', $actor->institution_id)
            ->findOrFail($studentId);

        $advisor = User::query()
            ->where('institution_id', $actor->institution_id)
            ->findOrFail($advisorUserId);

        AdvisorAssignment::query()
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return AdvisorAssignment::query()->create([
            'institution_id' => $student->institution_id,
            'advisor_user_id' => $advisor->id,
            'student_id' => $student->id,
            'assigned_at' => Carbon::now(),
            'is_active' => true,
            'assigned_by' => $actor->id,
            'assignment_reason' => $reason,
        ])->load(['advisor.person', 'student.person', 'student.programme']);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function myAdvisees(User $advisor): Collection
    {
        $assignments = AdvisorAssignment::query()
            ->where('institution_id', $advisor->institution_id)
            ->where('advisor_user_id', $advisor->id)
            ->where('is_active', true)
            ->with(['student.person', 'student.programme'])
            ->orderBy('assigned_at')
            ->get();

        return $assignments->map(function (AdvisorAssignment $assignment): array {
            $student = $assignment->student;
            abort_unless($student instanceof Student, 422);
            $audit = $this->audit->audit($student);
            $lastNote = AdvisoryNote::query()
                ->where('student_id', $student->id)
                ->orderByDesc('created_at')
                ->first();
            $latestGpa = TermGpa::query()
                ->where('student_id', $student->id)
                ->where('is_published', true)
                ->orderByDesc('published_at')
                ->first();
            $cgpa = (float) ($latestGpa?->cgpa ?? 0);

            return [
                'assignment_id' => $assignment->id,
                'assigned_at' => $assignment->assigned_at,
                'student' => [
                    'id' => $student->id,
                    'student_number' => $student->student_number,
                    'full_name' => $student->person?->full_name,
                    'programme' => $student->programme?->name,
                    'status' => $student->status,
                    'year_level' => $student->current_year_level,
                    'cgpa' => $cgpa,
                ],
                'completion_percent' => $audit['completion_percent'],
                'at_risk' => $audit['at_risk'] || ($cgpa > 0 && $cgpa < 2.0),
                'credits_remaining' => $audit['credits_remaining'],
                'last_note_at' => $lastNote?->created_at,
            ];
        });
    }

    public function addNote(User $advisor, string $studentId, array $payload): AdvisoryNote
    {
        $this->assertAssignedOrManager($advisor, $studentId);

        return AdvisoryNote::query()->create([
            'institution_id' => $advisor->institution_id,
            'student_id' => $studentId,
            'advisor_user_id' => $advisor->id,
            'note_type' => $payload['note_type'] ?? 'GENERAL',
            'note_text' => $payload['note_text'],
            'is_confidential' => $payload['is_confidential'] ?? true,
            'visible_to_student' => $payload['visible_to_student'] ?? false,
            'follow_up_status' => $payload['follow_up_status'] ?? 'NONE',
            'follow_up_at' => $payload['follow_up_at'] ?? null,
        ])->load(['advisor.person', 'student.person']);
    }

    /** @return Collection<int, AdvisoryNote> */
    public function notesForStudent(User $actor, string $studentId, bool $studentView = false): Collection
    {
        $query = AdvisoryNote::query()
            ->where('institution_id', $actor->institution_id)
            ->where('student_id', $studentId)
            ->with(['advisor.person'])
            ->orderByDesc('created_at');

        if ($studentView) {
            $query->where('visible_to_student', true);
        } else {
            $this->assertAssignedOrManager($actor, $studentId);
        }

        return $query->limit(50)->get();
    }

    public function requestSession(User $studentUser, array $payload): AdvisingSession
    {
        $student = Student::query()->where('person_id', $studentUser->person_id)->firstOrFail();
        $assignment = AdvisorAssignment::query()
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->first();

        if ($assignment === null) {
            throw ValidationException::withMessages([
                'advisor' => ['No academic advisor is assigned to you yet.'],
            ]);
        }

        return AdvisingSession::query()->create([
            'institution_id' => $student->institution_id,
            'student_id' => $student->id,
            'advisor_user_id' => $assignment->advisor_user_id,
            'scheduled_at' => $payload['scheduled_at'],
            'status' => 'REQUESTED',
            'mode' => $payload['mode'] ?? 'IN_PERSON',
            'topic' => $payload['topic'] ?? null,
        ])->load(['advisor.person', 'student.person']);
    }

    /** @return Collection<int, AdvisingSession> */
    public function sessionsForAdvisor(User $advisor): Collection
    {
        return AdvisingSession::query()
            ->where('advisor_user_id', $advisor->id)
            ->where('institution_id', $advisor->institution_id)
            ->with(['student.person', 'student.programme'])
            ->orderByDesc('scheduled_at')
            ->limit(50)
            ->get();
    }

    public function updateSessionStatus(User $advisor, string $sessionId, string $status, ?string $outcome = null): AdvisingSession
    {
        $session = AdvisingSession::query()
            ->where('institution_id', $advisor->institution_id)
            ->where('advisor_user_id', $advisor->id)
            ->findOrFail($sessionId);

        $session->update([
            'status' => $status,
            'outcome' => $outcome,
        ]);

        return $session->fresh(['student.person', 'advisor.person']) ?? $session;
    }

    /** @return Collection<int, AdvisorAssignment> */
    public function allAssignments(string $institutionId): Collection
    {
        return AdvisorAssignment::query()
            ->where('institution_id', $institutionId)
            ->where('is_active', true)
            ->with(['advisor.person', 'student.person', 'student.programme'])
            ->orderByDesc('assigned_at')
            ->get();
    }

    public function myAdvisor(Student $student): ?AdvisorAssignment
    {
        return AdvisorAssignment::query()
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->with(['advisor.person'])
            ->first();
    }

    private function assertAssignedOrManager(User $actor, string $studentId): void
    {
        if ($actor->scopesFor('advising.assignment.manage') !== []) {
            return;
        }

        $assigned = AdvisorAssignment::query()
            ->where('advisor_user_id', $actor->id)
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->exists();

        if (! $assigned) {
            throw ValidationException::withMessages([
                'student_id' => ['You are not the assigned advisor for this student.'],
            ]);
        }
    }
}
