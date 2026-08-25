<?php

declare(strict_types=1);

namespace App\Modules\Platform\Support;

use Illuminate\Support\Facades\Crypt;
use RuntimeException;

/**
 * Storage rules for restricted identifiers — national ID, passport number.
 *
 * Three columns work together and each has one job:
 *
 *  - `*_encrypted` holds the ciphertext, readable only by a user with `admission.identity.view`.
 *  - `*_hash` is a keyed blind index, so duplicates can be detected and lookups performed without the
 *    plaintext ever appearing in a query, a log or an index page. It is HMAC rather than a plain digest
 *    because the identifier space is small enough to brute-force a bare SHA-256 offline.
 *  - `*_masked` is what everyone else sees, and is safe to render in lists and letters.
 */
final class SensitiveField
{
    public static function encrypt(?string $value): ?string
    {
        $value = self::normalise($value);

        return $value === null ? null : Crypt::encryptString($value);
    }

    public static function decrypt(?string $ciphertext): ?string
    {
        if ($ciphertext === null || $ciphertext === '') {
            return null;
        }

        return Crypt::decryptString($ciphertext);
    }

    public static function blindIndex(?string $value): ?string
    {
        $value = self::normalise($value);

        return $value === null ? null : hash_hmac('sha256', mb_strtoupper($value), self::indexKey());
    }

    public static function mask(?string $value, int $visible = 4): ?string
    {
        $value = self::normalise($value);

        if ($value === null) {
            return null;
        }

        if (mb_strlen($value) <= $visible) {
            return str_repeat('•', mb_strlen($value));
        }

        return str_repeat('•', mb_strlen($value) - $visible).mb_substr($value, -$visible);
    }

    /** @return array{encrypted: string|null, hash: string|null, masked: string|null} */
    public static function protect(?string $value): array
    {
        return [
            'encrypted' => self::encrypt($value),
            'hash' => self::blindIndex($value),
            'masked' => self::mask($value),
        ];
    }

    private static function normalise(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/\s+/', '', trim($value)) ?? '';

        return $value === '' ? null : $value;
    }

    private static function indexKey(): string
    {
        $key = config('admission.identity.index_key');

        if (is_string($key) && $key !== '') {
            return $key;
        }

        $appKey = (string) config('app.key');

        if ($appKey === '') {
            throw new RuntimeException('No IDENTITY_INDEX_KEY and no APP_KEY: refusing to compute a blind index with an empty key.');
        }

        // Derived, not reused: a separate HKDF-style label keeps the index key distinct from the
        // encryption key even when an operator has not provisioned a dedicated one.
        return hash_hmac('sha256', 'admission:identity-blind-index:v1', $appKey, true);
    }
}
