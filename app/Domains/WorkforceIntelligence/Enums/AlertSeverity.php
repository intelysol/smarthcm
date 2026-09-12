<?php

namespace App\Domains\WorkforceIntelligence\Enums;

enum AlertSeverity: string
{
    case INFO = 'INFO';
    case WARNING = 'WARNING';
    case CRITICAL = 'CRITICAL';
}
