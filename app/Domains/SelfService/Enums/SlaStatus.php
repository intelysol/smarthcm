<?php

namespace App\Domains\SelfService\Enums;

enum SlaStatus: string
{
    case RUNNING = 'running';
    case PAUSED = 'paused';
    case MET = 'met';
    case WARNING = 'warning';
    case BREACHED = 'breached';
}
