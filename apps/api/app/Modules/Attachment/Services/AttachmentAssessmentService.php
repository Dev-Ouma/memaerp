<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Services;

use App\Modules\Attachment\Models\AttachmentAssessment;
use App\Modules\Attachment\Models\AttachmentPlacement;
use App\Modules\Iam\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class AttachmentAssessmentService
{
    /** @param array<string, mixed> $payload */
    public function submitAssessment(User $actor, string $placementId, array $payload): AttachmentAssessment
    {
        $placement = AttachmentPlacement::query()
            ->where('institution_id', $actor->institution_id)
            ->findOrFail($placementId);

        $type = (string) $payload['assessment_type'];

        if ($type === AttachmentAssessment::TYPE_UNIVERSITY) {
            if ((string) $placement->university_supervisor_id !== (string) $actor->id) {
                throw ValidationException::withMessages(['supervisor' => 'You are not the assigned university supervisor.']);
            }
        }

        $assessment = AttachmentAssessment::query()->firstOrNew([
            'placement_id' => $placement->id,
            'assessment_type' => $type,
        ]);

        $assessment->fill([
            'score' => (float) $payload['score'],
            'max_score' => (float) ($payload['max_score'] ?? 100),
            'comments' => $payload['comments'] ?? null,
            'assessed_by' => $actor->id,
            'assessor_name' => $payload['assessor_name'] ?? $actor->person?->full_name,
            'assessed_at' => Carbon::now(),
            'status' => 'SUBMITTED',
        ]);
        $assessment->save();

        $this->maybeCompletePlacement($placement->fresh(['assessments']));

        return $assessment->fresh();
    }

    /** @param array<string, mixed> $payload */
    public function recordHostEvaluation(User $actor, string $placementId, array $payload): AttachmentAssessment
    {
        $placement = AttachmentPlacement::query()
            ->where('institution_id', $actor->institution_id)
            ->findOrFail($placementId);

        $assessment = AttachmentAssessment::query()->updateOrCreate(
            [
                'placement_id' => $placement->id,
                'assessment_type' => AttachmentAssessment::TYPE_HOST,
            ],
            [
                'score' => (float) $payload['score'],
                'max_score' => (float) ($payload['max_score'] ?? 100),
                'comments' => $payload['comments'] ?? null,
                'assessed_by' => $actor->id,
                'assessor_name' => $payload['assessor_name'] ?? 'Host Supervisor',
                'assessed_at' => Carbon::now(),
                'status' => 'SUBMITTED',
            ],
        );

        $this->maybeCompletePlacement($placement->fresh(['assessments']));

        return $assessment->fresh();
    }

    private function maybeCompletePlacement(AttachmentPlacement $placement): void
    {
        $assessments = $placement->assessments
            ->where('status', 'SUBMITTED')
            ->keyBy('assessment_type');

        if (! $assessments->has(AttachmentAssessment::TYPE_HOST)
            || ! $assessments->has(AttachmentAssessment::TYPE_UNIVERSITY)) {
            return;
        }

        $host = $assessments->get(AttachmentAssessment::TYPE_HOST);
        $uni = $assessments->get(AttachmentAssessment::TYPE_UNIVERSITY);

        abort_unless($host instanceof AttachmentAssessment && $uni instanceof AttachmentAssessment, 422);

        $hostPct = ((float) $host->score / max((float) $host->max_score, 1)) * 100;
        $uniPct = ((float) $uni->score / max((float) $uni->max_score, 1)) * 100;
        $composite = round(($hostPct * 0.4) + ($uniPct * 0.6), 2);

        $placement->update([
            'status' => 'COMPLETED',
            'completed_at' => Carbon::now(),
            'final_grade' => $composite,
            'grade_letter' => $this->letterGrade($composite),
        ]);
    }

    private function letterGrade(float $score): string
    {
        return match (true) {
            $score >= 70 => 'A',
            $score >= 60 => 'B',
            $score >= 50 => 'C',
            $score >= 40 => 'D',
            default => 'F',
        };
    }
}
