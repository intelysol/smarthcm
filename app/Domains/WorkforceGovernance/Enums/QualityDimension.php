<?php

namespace App\Domains\WorkforceGovernance\Enums;

enum QualityDimension: string
{
    case COMPLETENESS = 'COMPLETENESS';
    case ACCURACY = 'ACCURACY';
    case CONSISTENCY = 'CONSISTENCY';
    case VALIDITY = 'VALIDITY';
    case UNIQUENESS = 'UNIQUENESS';
    case TIMELINESS = 'TIMELINESS';
    case INTEGRITY = 'INTEGRITY';
    case CONFORMITY = 'CONFORMITY';
}
