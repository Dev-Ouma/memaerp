<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'supervisor_id', 'role', 'status', 'assigned_on', 'ended_on', 'notes'])]
final class PgSupervisorAllocation extends Model
{
    protected function casts(): array
    {
        return ['assigned_on' => 'date', 'ended_on' => 'date'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(PgSupervisor::class, 'supervisor_id');
    }
}
