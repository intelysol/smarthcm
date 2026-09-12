<?php

namespace App\Domains\EmployeeDocuments\Enums;

enum RequirementStatus: string
{
    case REQUIRED = 'required';
    case OPTIONAL = 'optional';
    case SUBMITTED = 'submitted';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case WAIVED = 'waived';
    case NOT_APPLICABLE = 'not_applicable';
}
