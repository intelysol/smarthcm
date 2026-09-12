<?php

namespace App\Domains\WorkforceGovernance\Enums;

enum KpiCertificationStatus: string
{
    case CERTIFIED = 'CERTIFIED';
    case UNCERTIFIED = 'UNCERTIFIED';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case DEPRECATED = 'DEPRECATED';
}
