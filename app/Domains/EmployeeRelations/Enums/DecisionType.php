<?php

namespace App\Domains\EmployeeRelations\Enums;

enum DecisionType: string
{
    case NO_ACTION = 'no_action';
    case COUNSELLING = 'counselling';
    case VERBAL_WARNING = 'verbal_warning';
    case WRITTEN_WARNING = 'written_warning';
    case FINAL_WARNING = 'final_warning';
    case TRAINING_REQUIRED = 'training_required';
    case CORRECTIVE_ACTION = 'corrective_action';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::NO_ACTION => 'No Action',
            self::COUNSELLING => 'Formal Counselling',
            self::VERBAL_WARNING => 'Verbal Warning',
            self::WRITTEN_WARNING => 'Written Warning',
            self::FINAL_WARNING => 'Final Written Warning',
            self::TRAINING_REQUIRED => 'Mandatory Training',
            self::CORRECTIVE_ACTION => 'Corrective Action Plan',
            self::OTHER => 'Other Action',
        };
    }
}
