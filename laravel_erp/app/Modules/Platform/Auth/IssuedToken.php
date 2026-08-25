<?php

declare(strict_types=1);

namespace App\Modules\Platform\Auth;

use App\Models\Platform\ApiToken;

/**
 * The one and only moment the plaintext token exists. It is never persisted and never logged.
 */
final class IssuedToken
{
    public function __construct(public readonly ApiToken $token, public readonly string $plainTextToken) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'access_token' => $this->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $this->token->expires_at?->toIso8601String(),
            'abilities' => $this->token->abilities,
        ];
    }
}
