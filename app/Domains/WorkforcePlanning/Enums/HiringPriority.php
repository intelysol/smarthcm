<?php

namespace App\Domains\WorkforcePlanning\Enums;

enum HiringPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';
}
