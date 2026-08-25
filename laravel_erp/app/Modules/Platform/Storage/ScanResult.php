<?php

declare(strict_types=1);

namespace App\Modules\Platform\Storage;

/**
 * The outcome of a malware scan. `NOT_SCANNED` is a distinct state from `CLEAN` on purpose: a file that
 * no scanner has looked at must never be reported as safe.
 */
final class ScanResult
{
    public const CLEAN = 'CLEAN';

    public const INFECTED = 'INFECTED';

    public const NOT_SCANNED = 'NOT_SCANNED';

    public const ERROR = 'ERROR';

    private function __construct(
        public readonly string $status,
        public readonly ?string $signature = null,
        public readonly ?string $detail = null,
    ) {}

    public static function clean(): self
    {
        return new self(self::CLEAN);
    }

    public static function infected(string $signature): self
    {
        return new self(self::INFECTED, $signature);
    }

    public static function notScanned(string $reason): self
    {
        return new self(self::NOT_SCANNED, null, $reason);
    }

    public static function error(string $detail): self
    {
        return new self(self::ERROR, null, $detail);
    }

    public function isClean(): bool
    {
        return $this->status === self::CLEAN;
    }

    public function isInfected(): bool
    {
        return $this->status === self::INFECTED;
    }
}
