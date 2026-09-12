<?php

namespace App\Domains\Engagement\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}
