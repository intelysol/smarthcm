<?php

namespace App\Domains\WorkforceAdmin\Enums;

enum ExceptionStatus: string
{
    case DETECTED = 'detected';
    case ASSIGNED = 'assigned';
    case INVESTIGATING = 'investigating';
    case ACTION_REQUIRED = 'action_required';
    case RESOLVED = 'resolved';
    case VERIFIED = 'verified';
    case CLOSED = 'closed';
}
