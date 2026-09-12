<?php

namespace App\Domains\Expenses\Enums;

enum ReimbursementStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
}
