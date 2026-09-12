<?php

namespace App\Domains\WorkforcePlanning\Enums;

enum WorkforceScenarioType: string
{
    case BASE = 'base';
    case GROWTH = 'growth';
    case COST_REDUCTION = 'cost_reduction';
    case HIRING_FREEZE = 'hiring_freeze';
    case RESTRUCTURING = 'restructuring';
    case EXPANSION = 'expansion';
}
