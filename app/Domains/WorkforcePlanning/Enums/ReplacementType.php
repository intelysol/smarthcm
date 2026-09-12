<?php

namespace App\Domains\WorkforcePlanning\Enums;

enum ReplacementType: string
{
    case IMMEDIATE = 'immediate';
    case DELAYED = 'delayed';
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
    case NONE = 'none';
}
