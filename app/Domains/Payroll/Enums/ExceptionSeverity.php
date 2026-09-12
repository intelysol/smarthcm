<?php

namespace App\Domains\Payroll\Enums;

enum ExceptionSeverity: string
{
    case BLOCKING = 'blocking';
    case WARNING = 'warning';
    case INFORMATIONAL = 'informational';
}
