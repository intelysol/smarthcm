<?php

namespace App\Domains\Career\Enums;

enum TalentReviewStatus: string
{
    case Draft = 'draft';
    case ManagerInput = 'manager_input';
    case HrReview = 'hr_review';
    case Calibration = 'calibration';
    case Completed = 'completed';
    case Published = 'published';
}
