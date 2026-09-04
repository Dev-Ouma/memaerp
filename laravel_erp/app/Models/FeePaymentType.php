<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code', 'name', 'category', 'mandatory', 'ledger_allocation', 'refund_policy', 'status',
])]
final class FeePaymentType extends Model
{
    protected function casts(): array
    {
        return ['mandatory' => 'boolean'];
    }
}
