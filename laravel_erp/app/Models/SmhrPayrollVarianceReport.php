<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['month', 'staff_count', 'gross', 'paye', 'variance', 'reason', 'amount', 'status'])]
final class SmhrPayrollVarianceReport extends Model
{
    protected $table = 'smhr_payroll_variance_reports';

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }
}
