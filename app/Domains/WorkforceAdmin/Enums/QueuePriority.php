<?php

namespace App\Domains\WorkforceAdmin\Enums;

enum QueuePriority: string
{
    case CRITICAL = 'critical';
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';
    case INFORMATIONAL = 'informational';
}
