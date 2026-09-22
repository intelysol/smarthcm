<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Enums;

enum PricingModel: string
{
    case FLAT = 'flat';
    case PER_UNIT = 'per_unit';
    case PER_SEAT = 'per_seat';
    case TIERED = 'tiered';
    case VOLUME = 'volume';
    case GRADUATED = 'graduated';
    case OVERAGE = 'overage';
}
