<?php

namespace App\Domains\Benefits\Enums;

enum ClaimStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case INFO_REQUIRED = 'info_required';
    case APPROVED = 'approved';
    case PARTIALLY_APPROVED = 'partially_approved';
    case REJECTED = 'rejected';
    case PAID = 'paid';
    case CLOSED = 'closed';
}
