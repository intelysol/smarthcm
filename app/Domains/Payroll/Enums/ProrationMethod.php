<?php

namespace App\Domains\Payroll\Enums;

enum ProrationMethod: string
{
    case CALENDAR_DAYS = 'calendar_days';
    case WORKING_DAYS = 'working_days';
    case FIXED_30_DAYS = 'fixed_30_days';
    case ACTUAL_HOURS = 'actual_hours';
    case NONE = 'none';

    public function label(): string
    {
        return match ($this) {
            self::CALENDAR_DAYS => 'Calendar Days (Actual Days in Month)',
            self::WORKING_DAYS => 'Working Days (Excluding Weekends/Holidays)',
            self::FIXED_30_DAYS => 'Fixed 30-Day Divisor',
            self::ACTUAL_HOURS => 'Actual Hours Worked',
            self::NONE => 'No Proration (Full Pay)',
        };
    }
}
