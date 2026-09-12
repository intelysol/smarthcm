<?php

namespace App\Domains\WorkforceOptimization\Enums;

enum OpportunityCategory: string
{
    case CAPACITY = 'CAPACITY';
    case CAPACITY_GAP = 'CAPACITY_GAP';
    case CAPACITY_SURPLUS = 'CAPACITY_SURPLUS';
    case COST = 'COST';
    case PRODUCTIVITY = 'PRODUCTIVITY';
    case PRODUCTIVITY_BOTTLENECK = 'PRODUCTIVITY_BOTTLENECK';
    case SKILLS = 'SKILLS';
    case SKILLS_GAP = 'SKILLS_GAP';
    case OVERTIME = 'OVERTIME';
    case OVERTIME_ANOMALY = 'OVERTIME_ANOMALY';
    case ABSENCE = 'ABSENCE';
    case VACANCY = 'VACANCY';
    case SCHEDULING = 'SCHEDULING';
    case WORKFORCE_RISK = 'WORKFORCE_RISK';
}
