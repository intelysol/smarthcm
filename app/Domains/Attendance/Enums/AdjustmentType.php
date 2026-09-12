<?php

namespace App\Domains\Attendance\Enums;

enum AdjustmentType: string
{
    case MISSED_PUNCH = 'missed_punch';
    case TIME_CORRECTION = 'time_correction';
    case BREAK_CORRECTION = 'break_correction';
    case ABSENCE_OVERRIDE = 'absence_override';
    case FULL_DAY_CREDIT = 'full_day_credit';
}
