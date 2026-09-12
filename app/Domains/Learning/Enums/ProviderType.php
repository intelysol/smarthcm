<?php

namespace App\Domains\Learning\Enums;

enum ProviderType: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
    case VENDOR = 'vendor';
    case UNIVERSITY = 'university';
    case CERTIFICATION_BODY = 'certification_body';
}
