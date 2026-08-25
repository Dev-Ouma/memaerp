<?php

declare(strict_types=1);

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IdempotencyRecord extends Model
{
    use HasUuids;

    protected $table = 'idempotency_keys';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'idempotency_key',
        'route',
        'principal',
        'request_hash',
        'response_status',
        'response_body',
        'locked_at',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'response_body' => 'array',
            'locked_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
