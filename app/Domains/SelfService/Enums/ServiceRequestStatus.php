<?php

namespace App\Domains\SelfService\Enums;

enum ServiceRequestStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case RECEIVED = 'received';
    case UNDER_REVIEW = 'under_review';
    case ASSIGNED = 'assigned';
    case WAITING_FOR_EMPLOYEE = 'waiting_for_employee';
    case WAITING_FOR_APPROVAL = 'waiting_for_approval';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case CLOSED = 'closed';
    case REOPENED = 'reopened';
}
