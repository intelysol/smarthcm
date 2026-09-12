<?php

namespace App\Domains\WorkforceCost\Enums;

enum AllocationMethod: string
{
    case DIRECT = 'direct';
    case PERCENTAGE = 'percentage';
    case HOURS_BASED = 'hours_based';
    case FTE_BASED = 'fte_based';
    case COST_DRIVER = 'cost_driver';
    case ACTIVITY_BASED = 'activity_based';
    case SCHEDULE_BASED = 'schedule_based';
}