<?php

namespace App\Domains\Onboarding\Enums;

enum DocumentVerificationStatus: string
{
    case PENDING = 'pending';
    case SUBMITTED = 'submitted';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case REQUIRES_CORRECTION = 'requires_correction';
}
