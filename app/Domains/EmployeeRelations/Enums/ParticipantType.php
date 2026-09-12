<?php

namespace App\Domains\EmployeeRelations\Enums;

enum ParticipantType: string
{
    case SUBJECT = 'subject';
    case REPORTER = 'reporter';
    case WITNESS = 'witness';
    case INVESTIGATOR = 'investigator';
    case MANAGER = 'manager';
    case HR = 'hr';
    case REVIEWER = 'reviewer';
    case DECISION_MAKER = 'decision_maker';
    case LEGAL = 'legal';
    case SUPPORT = 'support';

    public function label(): string
    {
        return match ($this) {
            self::SUBJECT => 'Subject',
            self::REPORTER => 'Reporter',
            self::WITNESS => 'Witness',
            self::INVESTIGATOR => 'Investigator',
            self::MANAGER => 'Manager',
            self::HR => 'HR Partner',
            self::REVIEWER => 'Reviewer',
            self::DECISION_MAKER => 'Decision Maker',
            self::LEGAL => 'Legal Counsel',
            self::SUPPORT => 'Support Person',
        };
    }
}
