<?php

namespace App\Domains\Expenses\Enums;

enum AdvanceStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PARTIALLY_DISBURSED = 'partially_disbursed';
    case DISBURSED = 'disbursed';
    case SETTLED = 'settled';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';
}
