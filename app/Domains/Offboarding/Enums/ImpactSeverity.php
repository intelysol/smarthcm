<?php

namespace App\Domains\Offboarding\Enums;

enum ImpactSeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case BLOCKING = 'blocking';
}
