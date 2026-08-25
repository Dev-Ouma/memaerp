<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Model;

class QrScanEvent extends Model
{
    protected $table = 'qr_scan_events';

    public $timestamps = false;

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'qr_verification_token_id',
        'presented_prefix',
        'result',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }
}
