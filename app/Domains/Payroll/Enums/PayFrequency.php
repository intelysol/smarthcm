<?php

namespace App\Domains\Payroll\Enums;

enum PayFrequency: string
{
    case MONTHLY = 'monthly';
    case BIWEEKLY = 'biweekly';
    case WEEKLY = 'weekly';
    case SEMI_MONTHLY = 'semi_monthly';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly',
            self::BIWEEKLY => 'Biweekly',
            self::WEEKLY => 'Weekly',
            self::SEMI_MONTHLY => 'Semi-Monthly',
        };
    }
}
