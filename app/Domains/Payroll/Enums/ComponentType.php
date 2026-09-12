<?php

namespace App\Domains\Payroll\Enums;

enum ComponentType: string
{
    case BASIC = 'basic';
    case ALLOWANCE = 'allowance';
    case BENEFIT = 'benefit';
    case BONUS = 'bonus';
    case COMMISSION = 'commission';
    case OVERTIME = 'overtime';
    case REIMBURSEMENT = 'reimbursement';
    case DEDUCTION = 'deduction';
    case TAX = 'tax';
    case PENSION = 'pension';
    case EMPLOYER_CONTRIBUTION = 'employer_contribution';
    case LOAN = 'loan';
    case ADVANCE = 'advance';
    case ADJUSTMENT = 'adjustment';
    case ARREAR = 'arrear';

    public function isEarning(): bool
    {
        return in_array($this, [
            self::BASIC,
            self::ALLOWANCE,
            self::BENEFIT,
            self::BONUS,
            self::COMMISSION,
            self::OVERTIME,
            self::REIMBURSEMENT,
            self::ARREAR,
        ], true);
    }

    public function isDeduction(): bool
    {
        return in_array($this, [
            self::DEDUCTION,
            self::TAX,
            self::PENSION,
            self::LOAN,
            self::ADVANCE,
        ], true);
    }
}
