<?php

declare(strict_types=1);

namespace App\Modules\Course\Contracts;

/**
 * Seat ledger for a semester offering. Enrollment records a student; this contract
 * is the only way the live headcount is allowed to change.
 */
interface OfferingCapacity
{
    public function increment(string $offeringId): void;

    public function decrement(string $offeringId): void;
}
