<?php

namespace App\Domains\Offboarding\Enums;

enum SettlementStatus: string
{
    case PENDING = 'pending';
    case CALCULATED = 'calculated';
    case APPROVED = 'approved';
    case PAID = 'paid';
    case HOLD = 'hold';
}
