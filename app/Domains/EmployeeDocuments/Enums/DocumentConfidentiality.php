<?php

namespace App\Domains\EmployeeDocuments\Enums;

enum DocumentConfidentiality: string
{
    case PUBLIC_TO_EMPLOYEE = 'PUBLIC_TO_EMPLOYEE';
    case EMPLOYEE_ONLY = 'EMPLOYEE_ONLY';
    case MANAGER = 'MANAGER';
    case HR = 'HR';
    case HR_CONFIDENTIAL = 'HR_CONFIDENTIAL';
    case RESTRICTED = 'RESTRICTED';
    case HIGHLY_RESTRICTED = 'HIGHLY_RESTRICTED';
}
