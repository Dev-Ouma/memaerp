<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentProviderEvent extends Model
{
    use HasUuids;

    protected $table = 'payment_provider_events';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'provider',
        'provider_event_id',
        'event_type',
        'received_at',
        'provider_timestamp',
        'nonce',
        'signature',
        'signature_algorithm',
        'signature_verified',
        'payload_ref',
        'payload_hash',
        'processing_status',
        'processed_at',
        'error_code',
        'error_detail',
        'ip_address',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'provider_timestamp' => 'datetime',
            'signature_verified' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }
}
