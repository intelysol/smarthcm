<?php

namespace App\Domains\Attendance\Enums;

enum OvertimeStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case PARTIALLY_APPROVED = 'partially_approved';
    case REJECTED = 'rejected';
}
