<?php

namespace App\Domains\Analytics\Enums;

enum HcmDimensionType: string
{
    case TENANT = 'tenant';
    case COMPANY = 'company';
    case BRANCH = 'branch';
    case DEPARTMENT = 'department';
    case LOCATION = 'location';
    case COST_CENTER = 'cost_center';
    case JOB_GRADE = 'job_grade';
    case DESIGNATION = 'designation';
    case MANAGER = 'manager';
    case EMPLOYMENT_STATUS = 'employment_status';
    case EMPLOYMENT_TYPE = 'employment_type';
    case TENURE_BAND = 'tenure_band';
    case AGE_BAND = 'age_band';
}
