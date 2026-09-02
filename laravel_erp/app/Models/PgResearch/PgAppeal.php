<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'category_id', 'period_id', 'reference', 'grounds', 'evidence_path', 'status', 'assigned_to', 'decision_notes', 'decided_by', 'decided_at', 'submitted_at'])]
final class PgAppeal extends Model
{
    public const TERMINAL = ['UPHELD', 'DISMISSED', 'WITHDRAWN'];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime', 'submitted_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(PgResearchCandidate::class, 'candidate_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PgAppealCategory::class, 'category_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PgAppealPeriod::class, 'period_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function dueAt(): ?\Illuminate\Support\Carbon
    {
        $sla = $this->category?->sla_days;

        return $sla ? $this->submitted_at->copy()->addDays($sla) : null;
    }

    public function isOverdue(): bool
    {
        return ! in_array($this->status, self::TERMINAL, true)
            && $this->dueAt()?->isPast() === true;
    }
}
