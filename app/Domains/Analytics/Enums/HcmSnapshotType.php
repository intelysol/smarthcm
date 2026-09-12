<?php

namespace App\Domains\Analytics\Enums;

enum HcmSnapshotType: string
{
    case DAILY = 'daily';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case ANNUAL = 'annual';
}
