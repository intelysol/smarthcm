<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Enums;

enum BillingInterval: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case SEMIANNUAL = 'semiannual';
    case ANNUAL = 'annual';

    public function months(): int
    {
        return match ($this) {
            self::MONTHLY => 1,
            self::QUARTERLY => 3,
            self::SEMIANNUAL => 6,
            self::ANNUAL => 12,
        };
    }
}
