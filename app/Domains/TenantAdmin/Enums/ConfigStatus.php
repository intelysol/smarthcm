<?php

namespace App\Domains\TenantAdmin\Enums;

enum ConfigStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case SUPERSEDED = 'SUPERSEDED';
    case ARCHIVED = 'ARCHIVED';
}
