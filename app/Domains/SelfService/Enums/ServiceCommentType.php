<?php

namespace App\Domains\SelfService\Enums;

enum ServiceCommentType: string
{
    case PUBLIC = 'public';
    case INTERNAL = 'internal';
}
