<?php

namespace App\Domains\Mobility\Enums;

enum EligibilityStatus: string
{
    case ELIGIBLE = 'eligible';
    case CONDITIONALLY_ELIGIBLE = 'conditionally_eligible';
    case NOT_YET_ELIGIBLE = 'not_yet_eligible';
    case INELIGIBLE = 'ineligible';
    case REQUIRES_REVIEW = 'requires_review';
}
