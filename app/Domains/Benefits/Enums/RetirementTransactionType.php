<?php

namespace App\Domains\Benefits\Enums;

enum RetirementTransactionType: string
{
    case OPENING = 'opening';
    case EMPLOYEE_CONTRIBUTION = 'employee_contribution';
    case EMPLOYER_CONTRIBUTION = 'employer_contribution';
    case EMPLOYER_MATCH = 'employer_match';
    case INVESTMENT_RETURN = 'investment_return';
    case ADJUSTMENT = 'adjustment';
    case WITHDRAWAL = 'withdrawal';
    case TRANSFER = 'transfer';
    case CORRECTION = 'correction';
    case CLOSURE = 'closure';

    public function isCredit(): bool
    {
        return in_array($this, [
            self::OPENING,
            self::EMPLOYEE_CONTRIBUTION,
            self::EMPLOYER_CONTRIBUTION,
            self::EMPLOYER_MATCH,
            self::INVESTMENT_RETURN,
        ], true);
    }

    public function isDebit(): bool
    {
        return in_array($this, [
            self::WITHDRAWAL,
            self::TRANSFER,
            self::CLOSURE,
        ], true);
    }
}
