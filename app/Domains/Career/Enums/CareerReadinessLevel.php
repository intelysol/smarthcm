<?php

namespace App\Domains\Career\Enums;

enum CareerReadinessLevel: string
{
    case NotReady = 'not_ready';
    case Developing = 'developing';
    case NearlyReady = 'nearly_ready';
    case Ready = 'ready';
    case ReadyNow = 'ready_now';
}
