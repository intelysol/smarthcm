<?php

namespace App\Domains\WorkforcePlanning\Enums;

enum PlanningType: string
{
    case ANNUAL = 'annual';
    case MULTI_YEAR = 'multi_year';
    case QUARTERLY = 'quarterly';
    case STRATEGIC = 'strategic';
    case PROJECT = 'project';
}
