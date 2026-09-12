<?php

namespace App\Domains\Attendance\Enums;

enum ConflictType: string
{
    case LEAVE_CONFLICT = 'leave_conflict';
    case OVERLAPPING_SHIFTS = 'overlapping_shifts';
    case INSUFFICIENT_REST_PERIOD = 'insufficient_rest_period';
    case DUPLICATE_ASSIGNMENT = 'duplicate_assignment';
    case EXCESSIVE_HOURS = 'excessive_hours';
    case INACTIVE_EMPLOYMENT = 'inactive_employment';
    case OVERTIME_RISK = 'overtime_risk';
}
