<?php

namespace App\Domains\SelfService\Enums;

enum ServiceLinkType: string
{
    case PARENT = 'parent';
    case CHILD = 'child';
    case RELATED = 'related';
    case MERGED_INTO = 'merged_into';
    case DUPLICATED_BY = 'duplicated_by';
    case CONVERTED_TO_CASE = 'converted_to_case';
}
