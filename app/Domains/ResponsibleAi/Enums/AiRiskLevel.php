<?php

namespace App\Domains\ResponsibleAi\Enums;

enum AiRiskLevel: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';
    case CRITICAL = 'CRITICAL';
    case PROHIBITED = 'PROHIBITED';
}
