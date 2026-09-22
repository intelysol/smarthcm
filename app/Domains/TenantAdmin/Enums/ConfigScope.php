<?php

namespace App\Domains\TenantAdmin\Enums;

enum ConfigScope: string
{
    case PLATFORM = 'PLATFORM';
    case TENANT = 'TENANT';
    case LEGAL_ENTITY = 'LEGAL_ENTITY';
    case BUSINESS_UNIT = 'BUSINESS_UNIT';
    case DEPARTMENT = 'DEPARTMENT';
    case LOCATION = 'LOCATION';
    case EMPLOYEE_GROUP = 'EMPLOYEE_GROUP';

    public function priority(): int
    {
        return match ($this) {
            self::EMPLOYEE_GROUP => 70,
            self::LOCATION => 60,
            self::DEPARTMENT => 50,
            self::BUSINESS_UNIT => 40,
            self::LEGAL_ENTITY => 30,
            self::TENANT => 20,
            self::PLATFORM => 10,
        };
    }
}
