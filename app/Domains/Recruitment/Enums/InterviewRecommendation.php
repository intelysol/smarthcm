<?php

namespace App\Domains\Recruitment\Enums;

enum InterviewRecommendation: string
{
    case STRONG_YES = 'strong_yes';
    case YES = 'yes';
    case NEUTRAL = 'neutral';
    case NO = 'no';
    case STRONG_NO = 'strong_no';
}
