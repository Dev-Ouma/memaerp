<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'scheduled_for', 'venue', 'chair_name', 'status', 'verdict', 'verdict_notes', 'verdict_recorded_by', 'verdict_recorded_at'])]
final class PgVivaExamination extends Model
{
    public const VERDICTS = ['PASS', 'PASS_MINOR', 'PASS_MAJOR', 'REEXAMINE', 'FAIL'];

    protected function casts(): array
    {
        return ['scheduled_for' => 'datetime', 'verdict_recorded_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verdict_recorded_by');
    }
}
