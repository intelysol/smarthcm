<?php

namespace App\Domains\EmployeeRelations\Enums;

enum FindingOutcome: string
{
    case SUBSTANTIATED = 'substantiated';
    case PARTIALLY_SUBSTANTIATED = 'partially_substantiated';
    case UNSUBSTANTIATED = 'unsubstantiated';
    case INCONCLUSIVE = 'inconclusive';
    case WITHDRAWN = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::SUBSTANTIATED => 'Substantiated',
            self::PARTIALLY_SUBSTANTIATED => 'Partially Substantiated',
            self::UNSUBSTANTIATED => 'Unsubstantiated',
            self::INCONCLUSIVE => 'Inconclusive',
            self::WITHDRAWN => 'Withdrawn',
        };
    }
}
