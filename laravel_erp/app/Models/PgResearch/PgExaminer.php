<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['candidate_id', 'examiner_name', 'examiner_type', 'institution', 'email', 'appointed_on', 'status'])]
final class PgExaminer extends Model
{
    protected function casts(): array
    {
        return ['appointed_on' => 'date'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(PgExaminerReport::class, 'examiner_id');
    }
}
