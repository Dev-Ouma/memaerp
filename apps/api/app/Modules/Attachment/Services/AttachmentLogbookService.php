<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Services;

use App\Modules\Attachment\Models\AttachmentAssessment;
use App\Modules\Attachment\Models\AttachmentPlacement;
use App\Modules\Attachment\Models\LogbookEntry;
use App\Modules\Iam\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class AttachmentLogbookService
{
    /** @param array<string, mixed> $payload */
    public function submitEntry(User $studentUser, string $placementId, array $payload): LogbookEntry
    {
        $student = \App\Modules\Student\Models\Student::query()
            ->where('person_id', $studentUser->person_id)
            ->firstOrFail();

        $placement = AttachmentPlacement::query()
            ->where('student_id', $student->id)
            ->where('status', 'ACTIVE')
            ->findOrFail($placementId);

        $weekNumber = (int) $payload['week_number'];

        $existing = LogbookEntry::query()
            ->where('placement_id', $placement->id)
            ->where('week_number', $weekNumber)
            ->first();

        if ($existing !== null && $existing->status === 'ENDORSED') {
            throw ValidationException::withMessages(['week_number' => 'This week is already endorsed and cannot be edited.']);
        }

        $data = [
            'placement_id' => $placement->id,
            'week_number' => $weekNumber,
            'week_start' => $payload['week_start'],
            'activities_summary' => $payload['activities_summary'],
            'skills_learned' => $payload['skills_learned'] ?? null,
            'hours_logged' => (float) ($payload['hours_logged'] ?? 0),
            'status' => 'SUBMITTED',
            'submitted_at' => Carbon::now(),
        ];

        if ($existing !== null) {
            $existing->update($data);

            return $existing->fresh();
        }

        return LogbookEntry::query()->create($data);
    }

    public function endorseEntry(User $actor, string $entryId, ?string $comment = null): LogbookEntry
    {
        $entry = LogbookEntry::query()
            ->with('placement')
            ->findOrFail($entryId);

        $placement = $entry->placement;
        abort_unless($placement instanceof AttachmentPlacement, 422);

        if ((string) $placement->institution_id !== (string) $actor->institution_id) {
            throw ValidationException::withMessages(['entry' => 'Entry not found.']);
        }

        if ($entry->status !== 'SUBMITTED') {
            throw ValidationException::withMessages(['status' => 'Only submitted entries can be endorsed.']);
        }

        $entry->update([
            'status' => 'ENDORSED',
            'endorsed_at' => Carbon::now(),
            'host_comment' => $comment,
        ]);

        return $entry->fresh();
    }

    /** @return Collection<int, LogbookEntry> */
    public function entriesForPlacement(string $placementId): Collection
    {
        return LogbookEntry::query()
            ->where('placement_id', $placementId)
            ->orderBy('week_number')
            ->get();
    }
}
