<?php

namespace App\Domains\Benefits\Enums;

enum LoanInstallmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case DEDUCTED_PAYROLL = 'deducted_payroll';
    case DIRECT_PAID = 'direct_paid';
    case MISSED = 'missed';
    case WAIVED = 'waived';
    case REFUNDED = 'refunded';

    public function isSettled(): bool
    {
        return in_array($this, [self::DEDUCTED_PAYROLL, self::DIRECT_PAID, self::WAIVED], true);
    }
}
