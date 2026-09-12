<?php

namespace App\Domains\Payroll\Enums;

enum RunStatus: string
{
    case DRAFT = 'draft';
    case VALIDATING = 'validating';
    case CALCULATING = 'calculating';
    case CALCULATED = 'calculated';
    case VALIDATION_FAILED = 'validation_failed';
    case UNDER_REVIEW = 'under_review';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case LOCKED = 'locked';
    case PAID = 'paid';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function isModifiable(): bool
    {
        return in_array($this, [self::DRAFT, self::CALCULATING, self::VALIDATION_FAILED, self::CALCULATED, self::UNDER_REVIEW], true);
    }
}
