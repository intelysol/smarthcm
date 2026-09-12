<?php

namespace App\Domains\WorkforceCost\Enums;

enum CostNature: string
{
    case ACTUAL = 'ACTUAL';
    case PLANNED = 'PLANNED';
    case FORECAST = 'FORECAST';
    case ESTIMATED = 'ESTIMATED';
    case ALLOCATED = 'ALLOCATED';
    case SCENARIO = 'SCENARIO';
}