<?php

namespace App\Domains\Recruitment\Enums;

enum OfferStatus: string
{
    case DRAFT = 'draft';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case WITHDRAWN = 'withdrawn';
}
