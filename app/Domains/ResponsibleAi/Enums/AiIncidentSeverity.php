<?php

namespace App\Domains\ResponsibleAi\Enums;

enum AiIncidentSeverity: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';
    case CRITICAL = 'CRITICAL';
}
