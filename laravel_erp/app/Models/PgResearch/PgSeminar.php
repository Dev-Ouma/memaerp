<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'seminar_type', 'scheduled_for', 'venue', 'panel_chair', 'status', 'outcome_notes', 'held_at'])]
final class PgSeminar extends Model
{
    protected function casts(): array
    {
        return ['scheduled_for' => 'datetime', 'held_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }
}
