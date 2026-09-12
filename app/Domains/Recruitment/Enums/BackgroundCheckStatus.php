<?php

namespace App\Domains\Recruitment\Enums;

enum BackgroundCheckStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case PASSED = 'passed';
    case FLAGGED = 'flagged';
    case FAILED = 'failed';
}
