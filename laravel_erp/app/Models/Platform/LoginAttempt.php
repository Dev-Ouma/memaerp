<?php

declare(strict_types=1);

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $table = 'login_attempts';

    public $timestamps = false;

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'email_hash',
        'user_id',
        'ip_address',
        'user_agent',
        'successful',
        'failure_reason',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'occurred_at' => 'datetime',
        ];
    }
}
