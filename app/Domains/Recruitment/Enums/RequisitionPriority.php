<?php

namespace App\Domains\Recruitment\Enums;

enum RequisitionPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';
}
