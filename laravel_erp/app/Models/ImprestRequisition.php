<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['requisition_no', 'applicant_name', 'department', 'vote_head', 'amount_requested', 'purpose', 'disbursement_mode', 'surrender_due_date', 'status'])]
final class ImprestRequisition extends Model
{
    protected $table = 'imprest_requisitions';
}
