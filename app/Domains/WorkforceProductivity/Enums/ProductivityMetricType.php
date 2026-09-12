<?php

namespace App\Domains\WorkforceProductivity\Enums;

enum ProductivityMetricType: string
{
    case VOLUME = 'volume';
    case TIME = 'time';
    case QUALITY = 'quality';
    case ECONOMIC = 'economic';
}
