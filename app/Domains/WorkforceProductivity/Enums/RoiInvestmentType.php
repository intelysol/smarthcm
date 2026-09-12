<?php

namespace App\Domains\WorkforceProductivity\Enums;

enum RoiInvestmentType: string
{
    case HIRING = 'hiring';
    case TRAINING = 'training';
    case AUTOMATION = 'automation';
    case TECHNOLOGY = 'technology';
    case RELOCATION = 'relocation';
    case RESKILLING = 'reskilling';
    case SCHEDULING_REDESIGN = 'scheduling_redesign';
    case PROCESS_IMPROVEMENT = 'process_improvement';
}
