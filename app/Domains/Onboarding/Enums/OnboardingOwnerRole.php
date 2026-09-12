<?php

namespace App\Domains\Onboarding\Enums;

enum OnboardingOwnerRole: string
{
    case EMPLOYEE = 'employee';
    case MANAGER = 'manager';
    case HR = 'hr';
    case IT = 'it';
    case FINANCE = 'finance';
    case SECURITY = 'security';
    case FACILITIES = 'facilities';
}
