<?php

namespace App\Domains\Attendance\Enums;

enum ExceptionType: string
{
    case LATE = 'late';
    case EARLY_DEPARTURE = 'early_departure';
    case MISSING_PUNCH = 'missing_punch';
    case ABSENT = 'absent';
    case UNAUTHORIZED_ABSENCE = 'unauthorized_absence';
    case UNEXPECTED_ATTENDANCE = 'unexpected_attendance';
    case OVERTIME = 'overtime';
    case INSUFFICIENT_HOURS = 'insufficient_hours';
    case SHIFT_CONFLICT = 'shift_conflict';
    case LEAVE_CONFLICT = 'leave_conflict';
    case HOLIDAY_WORK = 'holiday_work';
    case WEEKEND_WORK = 'weekend_work';
    case DUPLICATE_PUNCH = 'duplicate_punch';
    case INVALID_DEVICE = 'invalid_device';
}
