<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['proposal_id', 'reader_id', 'verdict', 'comments', 'score', 'reviewed_by', 'reviewed_at'])]
final class PgProposalReview extends Model
{
    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime', 'score' => 'decimal:2'];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(PgProposal::class, 'proposal_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
