<?php

namespace App\Domains\Payroll\Enums;

enum CalculationType: string
{
    case FIXED = 'fixed';
    case PERCENTAGE_OF_BASIC = 'percentage_of_basic';
    case PERCENTAGE_OF_GROSS = 'percentage_of_gross';
    case FORMULA = 'formula';
    case HOURLY_RATE = 'hourly_rate';
    case ATTENDANCE_BASED = 'attendance_based';
    case CUSTOM_RULE = 'custom_rule';
}
