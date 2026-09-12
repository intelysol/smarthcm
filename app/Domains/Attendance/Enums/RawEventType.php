<?php

namespace App\Domains\Attendance\Enums;

enum RawEventType: string
{
    case IN = 'IN';
    case OUT = 'OUT';
    case BREAK_IN = 'BREAK_IN';
    case BREAK_OUT = 'BREAK_OUT';
    case UNKNOWN = 'UNKNOWN';
}
