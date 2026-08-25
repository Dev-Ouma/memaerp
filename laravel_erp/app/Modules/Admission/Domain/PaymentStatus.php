<?php

declare(strict_types=1);

namespace App\Modules\Admission\Domain;

enum PaymentStatus: string
{
    case NotStarted = 'NOT_STARTED';
    case Initiated = 'INITIATED';
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case Failed = 'FAILED';
    case Cancelled = 'CANCELLED';
    case Expired = 'EXPIRED';
    case Reversed = 'REVERSED';
    case Refunded = 'REFUNDED';
    case Waived = 'WAIVED';

    public function unlocksSubmission(): bool
    {
        return $this === self::Paid || $this === self::Waived;
    }
}
