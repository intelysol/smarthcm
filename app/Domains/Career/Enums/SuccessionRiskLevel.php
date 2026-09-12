<?php

namespace App\Domains\Career\Enums;

enum SuccessionRiskLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';
}
