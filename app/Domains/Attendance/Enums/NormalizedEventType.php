<?php

namespace App\Domains\Attendance\Enums;

enum NormalizedEventType: string
{
    case CHECK_IN = 'check_in';
    case CHECK_OUT = 'check_out';
    case BREAK_START = 'break_start';
    case BREAK_END = 'break_end';
    case UNKNOWN = 'unknown';
}
