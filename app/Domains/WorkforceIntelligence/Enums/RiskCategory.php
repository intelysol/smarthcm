<?php

namespace App\Domains\WorkforceIntelligence\Enums;

enum RiskCategory: string
{
    case PEOPLE = 'PEOPLE';
    case CAPACITY = 'CAPACITY';
    case SKILLS = 'SKILLS';
    case COST = 'COST';
    case PRODUCTIVITY = 'PRODUCTIVITY';
    case VACANCY = 'VACANCY';
    case COMPLIANCE = 'COMPLIANCE';
}
