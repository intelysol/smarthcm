<?php

namespace App\Domains\Expenses\Enums;

enum ExceptionStatus: string
{
    case OPEN = 'open';
    case UNDER_REVIEW = 'under_review';
    case RESOLVED = 'resolved';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case WAIVED = 'waived';
}
