<?php

namespace App\Domains\Expenses\Enums;

enum SettlementType: string
{
    case CLAIM_OFFSET = 'claim_offset';
    case EMPLOYEE_REFUND = 'employee_refund';
    case PAYROLL_RECOVERY = 'payroll_recovery';
    case CASH_SETTLEMENT = 'cash_settlement';
}
