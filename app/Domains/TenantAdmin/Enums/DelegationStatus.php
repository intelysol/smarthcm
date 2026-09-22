<?php

namespace App\Domains\TenantAdmin\Enums;

enum DelegationStatus: string
{
    case ACTIVE = 'ACTIVE';
    case EXPIRED = 'EXPIRED';
    case REVOKED = 'REVOKED';
}
