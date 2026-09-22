<?php

namespace App\Domains\AiOperations\Enums;

enum AiImprovementStatus: string
{
    case IDENTIFIED = 'IDENTIFIED';
    case TRIAGED = 'TRIAGED';
    case ASSIGNED = 'ASSIGNED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case VALIDATING = 'VALIDATING';
    case COMPLETED = 'COMPLETED';
    case REJECTED = 'REJECTED';
}
