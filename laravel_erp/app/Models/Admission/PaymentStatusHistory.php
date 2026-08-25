<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Model;

class PaymentStatusHistory extends Model
{
    protected $table = 'payment_status_history';

    public $timestamps = false;

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'admission_application_id',
        'application_payment_attempt_id',
        'from_status',
        'to_status',
        'reason_code',
        'actor_user_id',
        'actor_role',
        'source_channel',
        'correlation_id',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
