<?php

namespace App\Domains\WorkforcePlanning\Enums;

enum WorkforceGapType: string
{
    case HEADCOUNT = 'headcount';
    case FTE = 'fte';
    case SKILL = 'skill';
    case COST = 'cost';
    case CAPACITY = 'capacity';
}
