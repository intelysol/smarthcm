<?php

namespace App\Domains\Recruitment\Enums;

enum RequisitionStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case OPEN = 'open';
    case HIRING = 'hiring';
    case FILLED = 'filled';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
    case ON_HOLD = 'on_hold';
}
