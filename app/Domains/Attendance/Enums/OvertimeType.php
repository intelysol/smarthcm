<?php

namespace App\Domains\Attendance\Enums;

enum OvertimeType: string
{
    case REGULAR_OT = 'regular_ot';
    case WEEKEND_OT = 'weekend_ot';
    case HOLIDAY_OT = 'holiday_ot';
    case NIGHT_OT = 'night_ot';
}
