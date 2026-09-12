<?php

namespace App\Domains\EmployeeRelations\Enums;

enum EvidenceStatus: string
{
    case RETAINED = 'retained';
    case SUPERSEDED = 'superseded';
    case RESTRICTED = 'restricted';
    case DISPOSED = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::RETAINED => 'Active & Retained',
            self::SUPERSEDED => 'Superseded',
            self::RESTRICTED => 'Access Restricted',
            self::DISPOSED => 'Disposed (Compliance)',
        };
    }
}
