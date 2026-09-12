<?php

namespace App\Domains\Attendance\Enums;

enum RosterStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case PUBLISHED = 'published';
    case LOCKED = 'locked';
    case CANCELLED = 'cancelled';
}
