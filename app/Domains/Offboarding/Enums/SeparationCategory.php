<?php

namespace App\Domains\Offboarding\Enums;

enum SeparationCategory: string
{
    case VOLUNTARY = 'voluntary';
    case INVOLUNTARY = 'involuntary';
    case RETIREMENT = 'retirement';
    case EXPIRY = 'expiry';
    case MUTUAL = 'mutual';
    case OTHER = 'other';
}
