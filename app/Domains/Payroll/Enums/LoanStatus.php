<?php

namespace App\Domains\Payroll\Enums;

enum LoanStatus: string
{
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case SETTLED = 'settled';
    case CANCELLED = 'cancelled';
}
