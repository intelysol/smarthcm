<?php

namespace App\Domains\EmployeeRelations\Enums;

enum AppealStatus: string
{
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case DECISION_PENDING = 'decision_pending';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Submitted',
            self::UNDER_REVIEW => 'Under Review',
            self::DECISION_PENDING => 'Decision Pending',
            self::RESOLVED => 'Resolved',
            self::REJECTED => 'Rejected',
            self::WITHDRAWN => 'Withdrawn',
            self::CLOSED => 'Closed',
        };
    }
}
