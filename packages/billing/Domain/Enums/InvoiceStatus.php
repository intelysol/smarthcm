<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case PAST_DUE = 'past_due';
    case VOID = 'void';
    case UNCOLLECTIBLE = 'uncollectible';

    public function isSettled(): bool
    {
        return $this === self::PAID;
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::ISSUED, self::PARTIALLY_PAID, self::PAST_DUE]);
    }
}
