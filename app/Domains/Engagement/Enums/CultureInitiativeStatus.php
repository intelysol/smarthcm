<?php

namespace App\Domains\Engagement\Enums;

enum CultureInitiativeStatus: string
{
    case Draft = 'draft';
    case Planned = 'planned';
    case Active = 'active';
    case Completed = 'completed';
    case OnHold = 'on_hold';
    case Cancelled = 'cancelled';
}
