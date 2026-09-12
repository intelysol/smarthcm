<?php

namespace App\Domains\Expenses\Enums;

enum ExceptionSeverity: string
{
    case WARNING = 'warning';
    case BLOCKER = 'blocker';
}
