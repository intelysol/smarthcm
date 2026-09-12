<?php

namespace App\Domains\Benefits\Enums;

enum LoanApplicationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case DISBURSED = 'disbursed';
    case CLOSED = 'closed';
}
