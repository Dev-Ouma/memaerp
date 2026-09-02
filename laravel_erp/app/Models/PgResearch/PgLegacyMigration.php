<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'batch_reference', 'source_module', 'source_reference', 'target_stage', 'artifacts', 'status', 'imported_by', 'imported_at', 'error_message'])]
final class PgLegacyMigration extends Model
{
    protected function casts(): array
    {
        return ['imported_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
