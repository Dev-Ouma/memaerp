<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentReconciliationException extends Model
{
    use HasUuids;

    protected $table = 'payment_reconciliation_exceptions';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'payment_reconciliation_id',
        'payment_transaction_id',
        'admission_application_id',
        'exception_type',
        'expected_amount',
        'actual_amount',
        'currency',
        'detail',
        'status',
        'raised_by',
        'resolved_by',
        'resolved_at',
        'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
            'detail' => 'array',
            'resolved_at' => 'datetime',
        ];
    }
}
