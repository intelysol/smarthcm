<?php

namespace App\Domains\Learning\Enums;

enum SessionStatus: string
{
    case SCHEDULED = 'scheduled';
    case OPEN = 'open';
    case FULL = 'full';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
