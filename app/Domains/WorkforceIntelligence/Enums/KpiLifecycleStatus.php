<?php

namespace App\Domains\WorkforceIntelligence\Enums;

enum KpiLifecycleStatus: string
{
    case DRAFT = 'DRAFT';
    case ACTIVE = 'ACTIVE';
    case DEPRECATED = 'DEPRECATED';
    case ARCHIVED = 'ARCHIVED';
}
