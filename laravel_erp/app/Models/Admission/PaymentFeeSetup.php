<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentFeeSetup extends Model
{
    use HasUuids;

    protected $table = 'payment_fee_setups';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'code',
        'name',
        'amount',
        'currency',
        'effective_from',
        'effective_to',
        'is_refundable',
        'policy_note',
        'admission_intake_id',
        'programme_offering_id',
        'allowed_channels',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_refundable' => 'boolean',
            'allowed_channels' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
