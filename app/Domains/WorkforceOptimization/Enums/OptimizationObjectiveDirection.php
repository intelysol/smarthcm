<?php

namespace App\Domains\WorkforceOptimization\Enums;

enum OptimizationObjectiveDirection: string
{
    case MINIMIZE = 'MINIMIZE';
    case MAXIMIZE = 'MAXIMIZE';
}
