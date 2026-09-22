<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case AUTHORIZED = 'authorized';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function isSuccess(): bool
    {
        return $this === self::SUCCEEDED;
    }
}
