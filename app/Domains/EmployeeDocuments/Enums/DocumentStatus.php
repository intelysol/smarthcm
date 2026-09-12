<?php

namespace App\Domains\EmployeeDocuments\Enums;

enum DocumentStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case ARCHIVED = 'archived';
    case REPLACED = 'replaced';
    case WAIVED = 'waived';
}
