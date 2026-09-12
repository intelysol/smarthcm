<?php

namespace App\Domains\Attendance\Enums;

enum ExceptionStatus: string
{
    case OPEN = 'open';
    case UNDER_REVIEW = 'under_review';
    case PENDING_EMPLOYEE = 'pending_employee';
    case PENDING_MANAGER = 'pending_manager';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case RESOLVED = 'resolved';
    case CANCELLED = 'cancelled';
}
