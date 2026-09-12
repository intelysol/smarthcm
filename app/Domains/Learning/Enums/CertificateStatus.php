<?php

namespace App\Domains\Learning\Enums;

enum CertificateStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case RENEWED = 'renewed';
    case REVOKED = 'revoked';
    case PENDING_VERIFICATION = 'pending_verification';
}
