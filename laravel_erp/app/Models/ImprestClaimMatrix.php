<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['workflow_code', 'claim_category', 'originating_unit', 'workflow_sequence', 'auto_escalation_hours', 'delegate_allowed', 'status'])]
final class ImprestClaimMatrix extends Model
{
    protected $table = 'imprest_claim_matrices';
}
