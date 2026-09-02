<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['budget_proposal_id', 'from_status', 'to_status', 'actor_user_id', 'reason', 'approved_amount', 'occurred_at'])]
final class BudgetProposalTransition extends Model
{
    protected function casts(): array
    {
        return ['approved_amount' => 'decimal:2', 'occurred_at' => 'immutable_datetime'];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(BudgetProposal::class, 'budget_proposal_id');
    }
}
