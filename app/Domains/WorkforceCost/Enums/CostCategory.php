<?php

namespace App\Domains\WorkforceCost\Enums;

enum CostCategory: string
{
    case DIRECT_LABOR = 'direct_labor';
    case INDIRECT_LABOR = 'indirect_labor';
    case BURDEN = 'burden';
    case ACQUISITION = 'acquisition';
    case DEVELOPMENT = 'development';
    case MOBILITY = 'mobility';
    case CONTRACTOR = 'contractor';
    case OPPORTUNITY = 'opportunity';
}