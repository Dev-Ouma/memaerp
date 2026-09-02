<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'waiver_type', 'reason', 'status', 'requested_by', 'decided_by', 'decided_at', 'decision_notes', 'expires_on'])]
final class PgEligibilityWaiver extends Model
{
    protected function casts(): array
    {
        return ['decided_at' => 'datetime', 'expires_on' => 'date'];
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
