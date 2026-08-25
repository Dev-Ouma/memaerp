<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasUuids;

    protected $table = 'payment_transactions';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'application_payment_attempt_id',
        'payment_provider_event_id',
        'fee_setup_id',
        'provider',
        'provider_transaction_ref',
        'amount',
        'currency',
        'expected_amount',
        'transaction_time',
        'received_at',
        'raw_payload_ref',
        'raw_payload_hash',
        'signature_verified',
        'status',
        'is_authoritative_fee',
        'reconciliation_state',
        'reversal_of_transaction_id',
        'refund_of_transaction_id',
        'recorded_by',
        'notes',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'transaction_time' => 'datetime',
            'received_at' => 'datetime',
            'signature_verified' => 'boolean',
            'is_authoritative_fee' => 'boolean',
        ];
    }
}
