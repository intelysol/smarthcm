<?php

namespace App\Domains\Attendance\Enums;

enum TimesheetStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case MANAGER_APPROVED = 'manager_approved';
    case HR_APPROVED = 'hr_approved';
    case REJECTED = 'rejected';
    case LOCKED = 'locked';
    case EXPORTED = 'exported';
}
