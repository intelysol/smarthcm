<?php

namespace App\Domains\Career\Enums;

enum PositionCriticality: string
{
    case Critical = 'critical';
    case BusinessCritical = 'business_critical';
    case LeadershipCritical = 'leadership_critical';
    case TechnicalCritical = 'technical_critical';
}
