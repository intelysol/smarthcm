<?php

namespace App\Domains\Lifecycle\Enums;

enum AcknowledgementStatus: string
{
    case NOT_REQUIRED = 'not_required';
    case PENDING = 'pending';
    case ACKNOWLEDGED = 'acknowledged';
    case DECLINED = 'declined';
    case EXPIRED = 'expired';
}
