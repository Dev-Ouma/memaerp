<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['proposal_ref', 'fiscal_year', 'trimester', 'department', 'description', 'requested_amount', 'approved_amount', 'status', 'submitted_by', 'current_approver_id', 'lock_version', 'submitted_at', 'decided_at'])]
final class BudgetProposal extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'submitted_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
        ];
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(BudgetProposalTransition::class);
    }
}
