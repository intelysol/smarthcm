<?php

namespace App\Domains\WorkforceIntelligence\Enums;

enum KpiCategory: string
{
    case HEADCOUNT = 'HEADCOUNT';
    case CAPACITY = 'CAPACITY';
    case PRODUCTIVITY = 'PRODUCTIVITY';
    case COST = 'COST';
    case RETENTION = 'RETENTION';
    case SKILLS = 'SKILLS';
    case COMPLIANCE = 'COMPLIANCE';
}
