<?php

namespace App\Domains\Engagement\Enums;

enum SuggestionStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Accepted = 'accepted';
    case Implemented = 'implemented';
    case Rejected = 'rejected';
    case Deferred = 'deferred';
    case Archived = 'archived';
}
