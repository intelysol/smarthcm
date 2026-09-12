<?php

namespace App\Domains\Expenses\Enums;

enum PolicyViolationAction: string
{
    case ALLOWED = 'allowed';
    case WARNING = 'warning';
    case EXCESS_REDUCED = 'excess_reduced';
    case BLOCKED = 'blocked';
    case EXCEPTION_REQUIRED = 'exception_required';
}
