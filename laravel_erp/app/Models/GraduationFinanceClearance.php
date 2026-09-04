<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['student_id', 'student_name', 'reg_no', 'programme', 'ledger_balance', 'last_payment_date', 'status'])]
final class GraduationFinanceClearance extends Model
{
    protected $table = 'graduation_finance_clearances';
}
