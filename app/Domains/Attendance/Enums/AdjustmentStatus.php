<?php

namespace App\Domains\Attendance\Enums;

enum AdjustmentStatus: string
{
    case PENDING = 'pending';
    case MANAGER_APPROVED = 'manager_approved';
    case HR_APPROVED = 'hr_approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
