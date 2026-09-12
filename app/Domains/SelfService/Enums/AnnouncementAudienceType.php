<?php

namespace App\Domains\SelfService\Enums;

enum AnnouncementAudienceType: string
{
    case TENANT = 'tenant';
    case COMPANY = 'company';
    case BRANCH = 'branch';
    case DEPARTMENT = 'department';
    case EMPLOYEE_GROUP = 'employee_group';
}
