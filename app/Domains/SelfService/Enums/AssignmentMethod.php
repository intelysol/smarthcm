<?php

namespace App\Domains\SelfService\Enums;

enum AssignmentMethod: string
{
    case MANUAL = 'manual';
    case ROUND_ROBIN = 'round_robin';
    case WORKLOAD_BASED = 'workload_based';
    case RULE_BASED = 'rule_based';
}
