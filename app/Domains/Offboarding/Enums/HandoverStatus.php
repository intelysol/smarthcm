<?php

namespace App\Domains\Offboarding\Enums;

enum HandoverStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case VERIFIED = 'verified';
}
