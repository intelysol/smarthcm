<?php

namespace App\Domains\WorkforceIntelligence\Enums;

enum KpiSecurityClassification: string
{
    case PUBLIC = 'PUBLIC';
    case INTERNAL = 'INTERNAL';
    case CONFIDENTIAL = 'CONFIDENTIAL';
    case RESTRICTED = 'RESTRICTED';
}
