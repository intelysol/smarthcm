<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Enums;

enum SubscriptionStatus: string
{
    case TRIALING = 'trialing';
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case PAUSED = 'paused';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function isUsable(): bool
    {
        return in_array($this, [self::TRIALING, self::ACTIVE, self::PAST_DUE]);
    }

    public function isPastDue(): bool
    {
        return $this === self::PAST_DUE;
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }
}
