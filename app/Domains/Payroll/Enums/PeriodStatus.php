<?php

namespace App\Domains\Payroll\Enums;

enum PeriodStatus: string
{
    case OPEN = 'open';
    case INPUT_COLLECTION = 'input_collection';
    case CALCULATING = 'calculating';
    case CALCULATED = 'calculated';
    case UNDER_REVIEW = 'under_review';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case LOCKED = 'locked';
    case PAID = 'paid';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function isLocked(): bool
    {
        return in_array($this, [self::LOCKED, self::PAID, self::CLOSED], true);
    }
}
