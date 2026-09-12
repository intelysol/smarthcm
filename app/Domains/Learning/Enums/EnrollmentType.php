<?php

namespace App\Domains\Learning\Enums;

enum EnrollmentType: string
{
    case SELF = 'self';
    case MANAGER = 'manager';
    case HR = 'hr';
    case MANDATORY = 'mandatory';
    case SYSTEM = 'system';
    case PERFORMANCE = 'performance';
    case COMPLIANCE = 'compliance';
}
