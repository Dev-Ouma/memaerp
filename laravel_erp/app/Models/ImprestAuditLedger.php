<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['imprest_ref', 'staff_name', 'staff_no', 'department', 'amount_due', 'issue_date', 'due_date', 'days_overdue', 'risk_category', 'recovery_status'])]
final class ImprestAuditLedger extends Model
{
    protected $table = 'imprest_audit_ledgers';
}
