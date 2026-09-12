<?php

namespace App\Domains\WorkforceGovernance\Enums;

enum MappingStatus: string
{
    case UNMAPPED = 'UNMAPPED';
    case MAPPED = 'MAPPED';
    case AMBIGUOUS = 'AMBIGUOUS';
    case CONFLICT = 'CONFLICT';
    case RETIRED = 'RETIRED';
    case PENDING_REVIEW = 'PENDING_REVIEW';
}
