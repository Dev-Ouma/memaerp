<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'period_label', 'report_stage', 'milestone_summary', 'supervisor_comment', 'status', 'submitted_at', 'decided_by', 'decided_at'])]
final class PgProgressReport extends Model
{
    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'decided_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
