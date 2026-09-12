<?php

namespace App\Domains\Lifecycle\Enums;

enum AssignmentType: string
{
    case TEMPORARY = 'temporary';
    case ACTING = 'acting';
    case SECONDMENT = 'secondment';
    case DEPUTATION = 'deputation';
}
