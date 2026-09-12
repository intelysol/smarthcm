<?php

namespace App\Domains\Offboarding\Enums;

enum ClearanceStatus: string
{
    case PENDING = 'pending';
    case CLEARED = 'cleared';
    case REJECTED = 'rejected';
    case WAIVED = 'waived';
    case BLOCKED = 'blocked';
}
