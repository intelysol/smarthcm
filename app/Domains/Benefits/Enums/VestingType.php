<?php

namespace App\Domains\Benefits\Enums;

enum VestingType: string
{
    case IMMEDIATE = 'immediate';
    case CLIFF = 'cliff';
    case GRADED = 'graded';
    case CUSTOMIZED = 'customized';
}
