<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'subject_type', 'subject_id', 'action', 'from_status', 'to_status', 'actor_id', 'payload', 'created_at'])]
final class PgResearchEvent extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['payload' => 'array', 'created_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
