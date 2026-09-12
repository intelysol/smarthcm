<?php

namespace App\Domains\WorkforcePlanning\Enums;

enum PlanPositionStatus: string
{
    case PLANNED = 'planned';
    case BUDGETED = 'budgeted';
    case OPEN = 'open';
    case OCCUPIED = 'occupied';
    case FROZEN = 'frozen';
    case CANCELLED = 'cancelled';
    case ELIMINATED = 'eliminated';
}
