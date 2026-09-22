<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Enums;

enum EntitlementType: string
{
    case LIMIT = 'limit';
    case BOOLEAN = 'boolean';
    case TIER = 'tier';
    case FEATURE = 'feature';
}
