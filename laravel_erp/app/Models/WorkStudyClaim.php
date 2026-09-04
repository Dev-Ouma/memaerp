<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['voucher_no', 'student_name', 'reg_no', 'timesheet_ref', 'gross_amount', 'fee_account_credit', 'cash_stipend', 'disbursement_mode', 'audit_approval', 'disbursement_status'])]
final class WorkStudyClaim extends Model
{
    protected $table = 'work_study_claims';
}
