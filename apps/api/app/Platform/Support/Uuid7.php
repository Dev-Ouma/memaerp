<?php

declare(strict_types=1);

namespace App\Platform\Support;

use Random\RandomException;

/**
 * UUID version 7 — time-ordered identifiers (RFC 9562).
 *
 * Random v4 UUIDs scatter inserts across the whole B-tree, which degrades index locality badly
 * once tables reach the tens of millions of rows this system will hold. v7 puts a millisecond
 * timestamp in the high bits, so new rows land together at the right edge of the index while
 * remaining globally unique and non-sequential enough not to leak volume.
 */
final class Uuid7
{
    /**
     * @throws RandomException
     */
    public static function generate(): string
    {
        $timestamp = (int) (microtime(true) * 1000);

        // 48 bits of millisecond timestamp.
        $bytes = pack('J', $timestamp);
        $bytes = substr($bytes, 2, 6);

        // 74 bits of randomness.
        $bytes .= random_bytes(10);

        // Version 7 in the high nibble of byte 6.
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x70);
        // RFC 9562 variant in the two high bits of byte 8.
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }

    /** Extract the creation time encoded in a v7 identifier. */
    public static function timestampOf(string $uuid): ?int
    {
        $hex = str_replace('-', '', $uuid);

        if (strlen($hex) !== 32) {
            return null;
        }

        return (int) hexdec(substr($hex, 0, 12));
    }
}
