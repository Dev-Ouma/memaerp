<?php

declare(strict_types=1);

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationToken extends Model
{
    use HasUuids;

    protected $table = 'email_verification_tokens';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'user_id',
        'token_hash',
        'sent_to',
        'expires_at',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }
}
