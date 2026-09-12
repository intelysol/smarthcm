<?php

namespace App\Domains\EmployeeRelations\Enums;

enum NoteVisibility: string
{
    case CASE_TEAM = 'case_team';
    case HR_ONLY = 'hr_only';
    case INVESTIGATOR_ONLY = 'investigator_only';
    case LEGAL_ONLY = 'legal_only';
    case DECISION_MAKER = 'decision_maker';

    public function label(): string
    {
        return match ($this) {
            self::CASE_TEAM => 'Full Case Team',
            self::HR_ONLY => 'HR Personnel Only',
            self::INVESTIGATOR_ONLY => 'Investigators Only',
            self::LEGAL_ONLY => 'Legal Counsel Only',
            self::DECISION_MAKER => 'Decision Makers & Reviewers',
        };
    }
}
