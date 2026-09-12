<?php

namespace App\Domains\Mobility\Enums;

enum AssignmentStatus: string
{
    case PLANNING = 'planning';
    case ACTIVE = 'active';
    case ON_HOLD = 'on_hold';
    case EXTENSION_PENDING = 'extension_pending';
    case REPATRIATING = 'repatriating';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
