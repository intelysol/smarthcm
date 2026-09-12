<?php

namespace App\Domains\WorkforcePlanning\Enums;

enum WorkforcePlanStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case LOCKED = 'locked';
    case ARCHIVED = 'archived';
    case REJECTED = 'rejected';
}
