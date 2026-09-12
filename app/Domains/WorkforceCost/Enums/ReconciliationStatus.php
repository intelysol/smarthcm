<?php

namespace App\Domains\WorkforceCost\Enums;

enum ReconciliationStatus: string
{
    case MATCHED = 'MATCHED';
    case MINOR_VARIANCE = 'MINOR_VARIANCE';
    case MAJOR_VARIANCE = 'MAJOR_VARIANCE';
    case MISSING_SOURCE = 'MISSING_SOURCE';
    case MISSING_MAPPING = 'MISSING_MAPPING';
    case ERROR = 'ERROR';
}