<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentReceipt extends Model
{
    use HasUuids;

    protected $table = 'payment_receipts';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'payment_transaction_id',
        'receipt_number',
        'amount',
        'currency',
        'payment_method',
        'issued_at',
        'issued_by',
        'generated_document_id',
        'checksum',
        'is_void',
        'void_reason',
        'voided_by',
        'voided_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'is_void' => 'boolean',
            'voided_at' => 'datetime',
        ];
    }
}
