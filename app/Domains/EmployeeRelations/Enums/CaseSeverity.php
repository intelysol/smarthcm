<?php

namespace App\Domains\EmployeeRelations\Enums;

enum CaseSeverity: string
{
    case INFORMATIONAL = 'informational';
    case MINOR = 'minor';
    case MODERATE = 'moderate';
    case SERIOUS = 'serious';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::INFORMATIONAL => 'Informational',
            self::MINOR => 'Minor',
            self::MODERATE => 'Moderate',
            self::SERIOUS => 'Serious',
            self::CRITICAL => 'Critical',
        };
    }
}
