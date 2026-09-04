<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'name', 'bank_name', 'account_number', 'integration_type', 'status',
])]
final class FeePaymentAccount extends Model
{
    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class, 'payment_account_id');
    }

    public function confirmedRevenue(): float
    {
        return (float) $this->payments()->where('status', 'CONFIRMED')->sum('amount');
    }
}
