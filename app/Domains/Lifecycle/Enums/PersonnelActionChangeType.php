<?php

namespace App\Domains\Lifecycle\Enums;

enum PersonnelActionChangeType: string
{
    case ADD = 'add';
    case REMOVE = 'remove';
    case REPLACE = 'replace';
    case UPDATE = 'update';
}
