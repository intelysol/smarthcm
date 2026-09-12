<?php

namespace App\Domains\Onboarding\Enums;

enum ProbationStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case DUE = 'due';
    case COMPLETED = 'completed';
    case EXTENDED = 'extended';
    case PASSED = 'passed';
    case FAILED = 'failed';
}
