<?php

namespace App\Domains\Mobility\Enums;

enum MobilityRequestStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case ELIGIBILITY_CLEARED = 'eligibility_cleared';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
