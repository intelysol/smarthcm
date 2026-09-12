<?php

namespace App\Domains\WorkforceGovernance\Enums;

enum QualitySeverity: string
{
    case CRITICAL = 'CRITICAL';
    case HIGH = 'HIGH';
    case MEDIUM = 'MEDIUM';
    case LOW = 'LOW';
    case INFORMATIONAL = 'INFORMATIONAL';
}
