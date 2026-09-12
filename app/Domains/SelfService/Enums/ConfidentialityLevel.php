<?php

namespace App\Domains\SelfService\Enums;

enum ConfidentialityLevel: string
{
    case NORMAL = 'normal';
    case CONFIDENTIAL = 'confidential';
    case HIGHLY_CONFIDENTIAL = 'highly_confidential';
    case RESTRICTED = 'restricted';
}
