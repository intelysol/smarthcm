<?php

namespace App\Domains\Engagement\Enums;

enum SurveyStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Scheduled = 'scheduled';
    case Open = 'open';
    case Closed = 'closed';
    case Archived = 'archived';
}
