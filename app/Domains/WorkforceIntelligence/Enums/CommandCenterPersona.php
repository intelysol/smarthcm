<?php

namespace App\Domains\WorkforceIntelligence\Enums;

enum CommandCenterPersona: string
{
    case EXECUTIVE = 'EXECUTIVE';
    case FINANCE = 'FINANCE';
    case HR = 'HR';
    case MANAGER = 'MANAGER';
    case WORKFORCE_PLANNER = 'WORKFORCE_PLANNER';
}
