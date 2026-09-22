<?php

namespace App\Domains\AiOperations\Enums;

enum AiReadinessStatus: string
{
    case PRODUCTION_READY = 'PRODUCTION_READY';
    case CONDITIONALLY_READY = 'CONDITIONALLY_READY';
    case BLOCKED = 'BLOCKED';
}
