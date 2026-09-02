<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'cycle', 'due_on', 'submitted_at', 'status', 'corrections_summary', 'verified_by', 'verified_at'])]
final class PgThesisResubmission extends Model
{
    protected function casts(): array
    {
        return ['due_on' => 'date', 'submitted_at' => 'datetime', 'verified_at' => 'datetime', 'cycle' => 'integer'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
