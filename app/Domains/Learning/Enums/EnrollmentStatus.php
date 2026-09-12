<?php

namespace App\Domains\Learning\Enums;

enum EnrollmentStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case ENROLLED = 'enrolled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case WITHDRAWN = 'withdrawn';
    case EXPIRED = 'expired';
    case FAILED = 'failed';
}
