<?php

namespace App\Domains\EmployeeAi\Enums;

enum ConciergeActionStatus: string
{
    case PROPOSED = 'PROPOSED';
    case CONFIRMED = 'CONFIRMED';
    case CANCELLED = 'CANCELLED';
    case SUBMITTED = 'SUBMITTED';
    case FAILED = 'FAILED';
}
