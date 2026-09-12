<?php

namespace App\Domains\WorkforceGovernance\Enums;

enum ContractStatus: string
{
    case HEALTHY = 'HEALTHY';
    case WARNING = 'WARNING';
    case BROKEN = 'BROKEN';
    case DEPRECATED = 'DEPRECATED';
}
