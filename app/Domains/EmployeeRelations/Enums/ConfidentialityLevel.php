<?php

namespace App\Domains\EmployeeRelations\Enums;

enum ConfidentialityLevel: string
{
    case STANDARD_CONFIDENTIAL = 'standard_confidential';
    case HIGHLY_CONFIDENTIAL = 'highly_confidential';
    case RESTRICTED = 'restricted';
    case LEGAL_RESTRICTED = 'legal_restricted';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD_CONFIDENTIAL => 'Standard Confidential',
            self::HIGHLY_CONFIDENTIAL => 'Highly Confidential',
            self::RESTRICTED => 'Restricted',
            self::LEGAL_RESTRICTED => 'Legal Restricted',
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::STANDARD_CONFIDENTIAL => 1,
            self::HIGHLY_CONFIDENTIAL => 2,
            self::RESTRICTED => 3,
            self::LEGAL_RESTRICTED => 4,
        };
    }
}
