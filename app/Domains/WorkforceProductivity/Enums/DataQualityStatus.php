<?php

namespace App\Domains\WorkforceProductivity\Enums;

enum DataQualityStatus: string
{
    case VALID = 'VALID';
    case INSUFFICIENT_DATA = 'INSUFFICIENT_DATA';
    case ZERO_DENOMINATOR = 'ZERO_DENOMINATOR';
    case ESTIMATED_INPUT = 'ESTIMATED_INPUT';
    case ANOMALOUS = 'ANOMALOUS';
}
