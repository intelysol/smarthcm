<?php

namespace App\Domains\EmployeeRelations\Enums;

enum AppealDecision: string
{
    case UPHELD = 'upheld';
    case PARTIALLY_UPHELD = 'partially_upheld';
    case OVERTURNED = 'overturned';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';
    case MODIFIED = 'modified';

    public function label(): string
    {
        return match ($this) {
            self::UPHELD => 'Appeal Upheld (Decision Overturned)',
            self::PARTIALLY_UPHELD => 'Partially Upheld',
            self::OVERTURNED => 'Original Decision Overturned',
            self::REJECTED => 'Appeal Rejected (Original Decision Stands)',
            self::WITHDRAWN => 'Appeal Withdrawn',
            self::MODIFIED => 'Decision Modified',
        };
    }
}
