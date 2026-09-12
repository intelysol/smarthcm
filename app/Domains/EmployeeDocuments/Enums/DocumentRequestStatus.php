<?php

namespace App\Domains\EmployeeDocuments\Enums;

enum DocumentRequestStatus: string
{
    case REQUESTED = 'requested';
    case SUBMITTED = 'submitted';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';
}
