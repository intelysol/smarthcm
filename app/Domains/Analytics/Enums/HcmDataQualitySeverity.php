<?php

namespace App\Domains\Analytics\Enums;

enum HcmDataQualitySeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
}
