<?php

namespace App\Domains\Attendance\Enums;

enum ConflictSeverity: string
{
    case WARNING = 'warning';
    case BLOCKING = 'blocking';
    case INFORMATIONAL = 'informational';
}
