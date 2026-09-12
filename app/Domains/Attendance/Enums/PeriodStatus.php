<?php

namespace App\Domains\Attendance\Enums;

enum PeriodStatus: string
{
    case OPEN = 'open';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case LOCKED = 'locked';
    case REOPENED = 'reopened';
    case CLOSED = 'closed';
}
