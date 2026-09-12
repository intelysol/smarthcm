<?php

namespace App\Domains\Learning\Enums;

enum ScoringMethod: string
{
    case PERCENTAGE = 'percentage';
    case POINTS = 'points';
    case PASS_FAIL = 'pass_fail';
    case RATING = 'rating';
}
