<?php

namespace App\Domains\Attendance\Enums;

enum BreakType: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case FIXED = 'fixed';
    case FLEXIBLE = 'flexible';
}
