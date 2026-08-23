<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use InvalidArgumentException;

final class TotpService
{
    private const string ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    public function verify(string $secret, string $code, ?int $timestamp = null): bool
    {
        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $counter = intdiv($timestamp ?? time(), 30);
        for ($offset = -1; $offset <= 1; $offset++) {
            if (hash_equals($this->at($secret, $counter + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    public function provisioningUri(string $secret, string $email, string $issuer = 'MEMA ERP'): string
    {
        $label = rawurlencode($issuer.':'.$email);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $label,
            $secret,
            rawurlencode($issuer),
        );
    }

    public function at(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);
        $binaryCounter = pack('N2', intdiv($counter, 0x100000000), $counter % 0x100000000);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $bytes): string
    {
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[(int) bindec(str_pad($chunk, 5, '0'))];
        }

        return $encoded;
    }

    private function base32Decode(string $value): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z2-7]/', '', $value) ?? '');
        if ($clean === '') {
            throw new InvalidArgumentException('Invalid TOTP secret.');
        }

        $bits = '';
        foreach (str_split($clean) as $character) {
            $position = strpos(self::ALPHABET, $character);
            if ($position === false) {
                throw new InvalidArgumentException('Invalid TOTP secret.');
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr((int) bindec($chunk));
            }
        }

        return $decoded;
    }
}
