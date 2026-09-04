<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['surrender_no', 'requisition_ref', 'staff_name', 'department', 'imprest_amount', 'actual_expenditure', 'unspent_refund', 'supplementary_claim', 'etims_compliance', 'audit_verdict', 'surrender_status'])]
final class ImprestSurrender extends Model
{
    protected $table = 'imprest_surrenders';
}
