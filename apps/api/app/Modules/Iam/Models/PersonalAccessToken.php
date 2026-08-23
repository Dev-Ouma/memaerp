<?php

declare(strict_types=1);

namespace App\Modules\Iam\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

final class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $table = 'iam.personal_access_tokens';

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'institution_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $token): void {
            if ($token->institution_id !== null || $token->tokenable_id === null) {
                return;
            }

            $token->institution_id = User::query()
                ->whereKey($token->tokenable_id)
                ->value('institution_id');
        });
    }
}
