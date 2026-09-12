<?php

namespace App\Domains\EmployeeDocuments\Enums;

enum VerificationStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case WAIVED = 'waived';
}
