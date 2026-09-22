<?php

namespace App\Domains\TenantAdmin\Enums;

enum OnboardingStep: int
{
    case COMPANY = 1;
    case ORGANIZATION = 2;
    case WORKFORCE = 3;
    case SECURITY = 4;
    case MODULES = 5;

    public function label(): string
    {
        return match ($this) {
            self::COMPANY => 'Company & General Setup',
            self::ORGANIZATION => 'Organization Architecture',
            self::WORKFORCE => 'Workforce & Employment Rules',
            self::SECURITY => 'Security & Admin Roles',
            self::MODULES => 'HCM Modules Activation',
        };
    }
}
