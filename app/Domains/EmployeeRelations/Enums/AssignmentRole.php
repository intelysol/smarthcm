<?php

namespace App\Domains\EmployeeRelations\Enums;

enum AssignmentRole: string
{
    case CASE_OWNER = 'case_owner';
    case HR_PARTNER = 'hr_partner';
    case INVESTIGATOR = 'investigator';
    case REVIEWER = 'reviewer';
    case DECISION_MAKER = 'decision_maker';
    case LEGAL_REVIEWER = 'legal_reviewer';

    public function label(): string
    {
        return match ($this) {
            self::CASE_OWNER => 'Case Owner',
            self::HR_PARTNER => 'HR Partner',
            self::INVESTIGATOR => 'Lead Investigator',
            self::REVIEWER => 'Case Reviewer',
            self::DECISION_MAKER => 'Decision Maker',
            self::LEGAL_REVIEWER => 'Legal Reviewer',
        };
    }
}
