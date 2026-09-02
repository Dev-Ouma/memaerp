<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['examiner_id', 'candidate_id', 'recommendation', 'score', 'remarks', 'submitted_at'])]
final class PgExaminerReport extends Model
{
    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'score' => 'decimal:2'];
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(PgExaminer::class, 'examiner_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }
}
