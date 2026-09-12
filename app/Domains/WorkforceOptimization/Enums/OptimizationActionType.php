<?php

namespace App\Domains\WorkforceOptimization\Enums;

enum OptimizationActionType: string
{
    case HIRE = 'HIRE';
    case HIRE_PERMANENT = 'HIRE_PERMANENT';
    case REDEPLOY = 'REDEPLOY';
    case REDEPLOY_INTERNAL = 'REDEPLOY_INTERNAL';
    case RESKILL = 'RESKILL';
    case TRAIN = 'TRAIN';
    case CONTRACT = 'CONTRACT';
    case CONTRACTOR_ENGAGE = 'CONTRACTOR_ENGAGE';
    case REDESIGN_SHIFT = 'REDESIGN_SHIFT';
    case SHIFT_REBALANCING = 'SHIFT_REBALANCING';
    case REDUCE_OVERTIME = 'REDUCE_OVERTIME';
    case REALLOCATE_WORK = 'REALLOCATE_WORK';
    case SCHEDULE_OPTIMIZE = 'SCHEDULE_OPTIMIZE';
    case AUTOMATE = 'AUTOMATE';
    case PROCESS_AUTOMATION = 'PROCESS_AUTOMATION';
    case RELOCATE = 'RELOCATE';
    case REPLACE = 'REPLACE';
    case DELAY_HIRING = 'DELAY_HIRING';
    case ACCELERATE_HIRING = 'ACCELERATE_HIRING';

    public function label(): string
    {
        return match ($this) {
            self::HIRE, self::HIRE_PERMANENT => 'Permanent Hire',
            self::REDEPLOY, self::REDEPLOY_INTERNAL => 'Internal Redeployment',
            self::RESKILL => 'Reskilling & Upskilling',
            self::TRAIN => 'Targeted Training',
            self::CONTRACT, self::CONTRACTOR_ENGAGE => 'Contingent Contractor',
            self::REDESIGN_SHIFT, self::SHIFT_REBALANCING => 'Shift Rebalancing',
            self::REDUCE_OVERTIME => 'Overtime Mitigation',
            self::REALLOCATE_WORK, self::SCHEDULE_OPTIMIZE => 'Workload Reallocation',
            self::AUTOMATE, self::PROCESS_AUTOMATION => 'Process Automation',
            self::RELOCATE => 'Workforce Relocation',
            self::REPLACE => 'Workforce Replacement',
            self::DELAY_HIRING => 'Hiring Delay',
            self::ACCELERATE_HIRING => 'Accelerated Hiring',
        };
    }
}
