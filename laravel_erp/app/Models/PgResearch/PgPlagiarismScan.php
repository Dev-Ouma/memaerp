<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'document_type', 'similarity_index', 'threshold', 'ai_index', 'ai_threshold', 'status', 'report_reference', 'scanned_at', 'reviewed_by', 'review_notes'])]
final class PgPlagiarismScan extends Model
{
    protected function casts(): array
    {
        return [
            'similarity_index' => 'decimal:2',
            'threshold' => 'decimal:2',
            'ai_index' => 'decimal:2',
            'ai_threshold' => 'decimal:2',
            'scanned_at' => 'datetime',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function withinThreshold(): bool
    {
        return (float) $this->similarity_index <= (float) $this->threshold;
    }

    /** An AI-content figure is only a flag once it is actually recorded. */
    public function aiFlagged(): bool
    {
        return $this->ai_index !== null && (float) $this->ai_index > (float) $this->ai_threshold;
    }
}
