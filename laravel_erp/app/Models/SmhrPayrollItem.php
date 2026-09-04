<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['staff_id', 'name', 'dept', 'bank', 'month', 'basic_pay', 'allowances', 'gross', 'paye', 'statutory', 'net_pay', 'status'])]
final class SmhrPayrollItem extends Model
{
    protected $table = 'smhr_payroll_items';

    protected function casts(): array
    {
        return [
            'basic_pay' => 'decimal:2',
            'allowances' => 'decimal:2',
            'gross' => 'decimal:2',
            'paye' => 'decimal:2',
            'statutory' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }
}
