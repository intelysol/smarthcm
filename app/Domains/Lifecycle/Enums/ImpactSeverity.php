<?php

namespace App\Domains\Lifecycle\Enums;

enum ImpactSeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case BLOCKING = 'blocking';
}
