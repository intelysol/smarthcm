<?php

namespace App\Domains\SelfService\Enums;

enum ServiceRequestPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';
    case CRITICAL = 'critical';
}
