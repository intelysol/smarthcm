<?php

namespace App\Domains\Payroll\Enums;

enum RunType: string
{
    case REGULAR = 'regular';
    case OFF_CYCLE = 'off_cycle';
    case FINAL_SETTLEMENT = 'final_settlement';
    case BONUS = 'bonus';
    case CORRECTION = 'correction';
    case EMERGENCY = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular Payroll',
            self::OFF_CYCLE => 'Off-Cycle Payroll',
            self::FINAL_SETTLEMENT => 'Final Settlement / Termination',
            self::BONUS => 'Bonus Payroll',
            self::CORRECTION => 'Correction Run',
            self::EMERGENCY => 'Emergency Advance',
        };
    }
}
