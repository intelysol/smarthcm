<?php

namespace App\Domains\Attendance\Enums;

enum ShiftType: string
{
    case NORMAL = 'normal';
    case OVERNIGHT = 'overnight';
    case FLEXIBLE = 'flexible';
    case SPLIT = 'split';
}
