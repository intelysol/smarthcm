<?php

namespace App\Domains\Attendance\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case EARLY_DEPARTURE = 'early_departure';
    case ABSENT = 'absent';
    case HALF_DAY = 'half_day';
    case ON_LEAVE = 'on_leave';
    case REST_DAY = 'rest_day';
    case HOLIDAY = 'holiday';
    case INCOMPLETE = 'incomplete';
    case EXCEPTION = 'exception';
    case OVERTIME = 'overtime';
}
