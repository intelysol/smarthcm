<?php

namespace App\Domains\Analytics\Enums;

enum HcmAlertSeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case CRITICAL = 'critical';
}
