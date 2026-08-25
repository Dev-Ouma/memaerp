<?php

declare(strict_types=1);

namespace App\Modules\Platform\Auth;

use App\Models\Platform\ApiToken;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Bearer tokens for the API.
 *
 * Only a SHA-256 hash of the token is stored, so a database disclosure does not yield usable
 * credentials, and the plaintext is returned exactly once at issue time. Lookup is by hash — a constant
 * work factor is unnecessary here because the token is 40+ bytes of CSPRNG output, not a human secret.
 */
final class ApiTokenService
{
    public const PREFIX = 'mema_at_';

    /** @param list<string> $abilities */
    public function issue(User $user, string $name, array $abilities = ['*'], ?int $ttlMinutes = null): IssuedToken
    {
        $plain = self::PREFIX.Str::random(48);

        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => $name,
            'token_hash' => self::hash($plain),
            'abilities' => $abilities,
            'expires_at' => $ttlMinutes !== null ? now()->addMinutes($ttlMinutes) : null,
            'created_ip' => request()?->ip(),
            'user_agent' => Str::limit((string) request()?->userAgent(), 250, ''),
        ]);

        return new IssuedToken($token, $plain);
    }

    public function resolve(string $plain): ?ApiToken
    {
        if (! str_starts_with($plain, self::PREFIX)) {
            return null;
        }

        $token = ApiToken::query()
            ->where('token_hash', self::hash($plain))
            ->whereNull('revoked_at')
            ->first();

        if ($token === null) {
            return null;
        }

        if ($token->expires_at !== null && $token->expires_at->isPast()) {
            return null;
        }

        return $token;
    }

    public function touch(ApiToken $token): void
    {
        // Coarse-grained so a busy client does not generate a write per request.
        if ($token->last_used_at === null || $token->last_used_at->lt(now()->subMinute())) {
            $token->forceFill(['last_used_at' => now()])->save();
        }
    }

    public function revoke(ApiToken $token): void
    {
        $token->forceFill(['revoked_at' => now()])->save();
    }

    public function revokeAllFor(User $user): int
    {
        return ApiToken::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }
}
