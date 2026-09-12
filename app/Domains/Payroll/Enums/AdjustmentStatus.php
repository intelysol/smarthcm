<?php

namespace App\Domains\Payroll\Enums;

enum AdjustmentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case APPLIED = 'applied';
}
