<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'plagiarism_scan_id', 'thesis_title', 'status', 'requested_at', 'decision_notes', 'decided_by', 'decided_at'])]
final class PgDefenceRequest extends Model
{
    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'decided_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(PgPlagiarismScan::class, 'plagiarism_scan_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
