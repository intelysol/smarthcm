<?php

namespace App\Domains\ResponsibleAi\Enums;

enum AiHumanOversight: string
{
    case NONE = 'NONE';
    case REVIEW = 'REVIEW';
    case APPROVAL = 'APPROVAL';
    case DUAL_APPROVAL = 'DUAL_APPROVAL';
    case COMMITTEE_REVIEW = 'COMMITTEE_REVIEW';
}
