<?php

namespace App\Domains\AiOperations\Enums;

enum AiFeedbackCategory: string
{
    case INCORRECT = 'INCORRECT';
    case OUTDATED = 'OUTDATED';
    case NOT_RELEVANT = 'NOT_RELEVANT';
    case UNSAFE = 'UNSAFE';
    case MISSING_INFORMATION = 'MISSING_INFORMATION';
    case WRONG_SOURCE = 'WRONG_SOURCE';
    case WRONG_ACTION = 'WRONG_ACTION';
    case TOO_SLOW = 'TOO_SLOW';
    case OTHER = 'OTHER';
}
