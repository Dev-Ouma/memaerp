<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'composite_score', 'final_grade', 'status', 'ratified_by', 'ratified_at', 'notes'])]
final class PgThesisMark extends Model
{
    protected function casts(): array
    {
        return ['composite_score' => 'decimal:2', 'ratified_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function ratifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ratified_by');
    }
}
