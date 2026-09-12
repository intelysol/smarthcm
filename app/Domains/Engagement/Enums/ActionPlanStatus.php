<?php

namespace App\Domains\Engagement\Enums;

enum ActionPlanStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
