<?php

namespace App\Domains\EmployeeRelations\Enums;

enum CaseStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case TRIAGE = 'triage';
    case ASSIGNED = 'assigned';
    case INVESTIGATION = 'investigation';
    case REVIEW = 'review';
    case DECISION = 'decision';
    case ACTION = 'action';
    case APPEAL = 'appeal';
    case CLOSED = 'closed';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';
    case CANCELLED = 'cancelled';
    case ON_HOLD = 'on_hold';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::TRIAGE => 'Under Triage',
            self::ASSIGNED => 'Assigned',
            self::INVESTIGATION => 'Under Investigation',
            self::REVIEW => 'Under Review',
            self::DECISION => 'Decision Pending',
            self::ACTION => 'Action in Progress',
            self::APPEAL => 'Under Appeal',
            self::CLOSED => 'Closed',
            self::REJECTED => 'Rejected',
            self::WITHDRAWN => 'Withdrawn',
            self::CANCELLED => 'Cancelled',
            self::ON_HOLD => 'On Hold',
            self::ARCHIVED => 'Archived',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [
            self::SUBMITTED,
            self::TRIAGE,
            self::ASSIGNED,
            self::INVESTIGATION,
            self::REVIEW,
            self::DECISION,
            self::ACTION,
            self::APPEAL,
            self::ON_HOLD,
        ], true);
    }
}
