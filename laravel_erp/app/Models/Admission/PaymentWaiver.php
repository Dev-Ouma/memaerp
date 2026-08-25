<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentWaiver extends Model
{
    use HasUuids;

    protected $table = 'payment_waivers';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'fee_setup_id',
        'amount_waived',
        'currency',
        'reason_code',
        'justification',
        'evidence_document_id',
        'approved_by',
        'approved_at',
        'status',
        'revoked_by',
        'revoked_at',
        'revocation_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount_waived' => 'decimal:2',
            'approved_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
