<?php

namespace App\Domains\WorkforceGovernance\Enums;

enum IssueLifecycleStatus: string
{
    case DETECTED = 'DETECTED';
    case OPEN = 'OPEN';
    case ASSIGNED = 'ASSIGNED';
    case INVESTIGATING = 'INVESTIGATING';
    case CORRECTION_REQUESTED = 'CORRECTION_REQUESTED';
    case CORRECTED = 'CORRECTED';
    case VALIDATED = 'VALIDATED';
    case RESOLVED = 'RESOLVED';
    case ACCEPTED_EXCEPTION = 'ACCEPTED_EXCEPTION';
}
