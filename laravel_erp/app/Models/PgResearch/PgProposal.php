<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['candidate_id', 'reader_id', 'title', 'abstract', 'version', 'status', 'manuscript_path', 'submitted_at'])]
final class PgProposal extends Model
{
    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'version' => 'integer'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(PgSupervisor::class, 'reader_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PgProposalReview::class, 'proposal_id');
    }
}
