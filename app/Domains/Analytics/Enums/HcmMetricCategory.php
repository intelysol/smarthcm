<?php

namespace App\Domains\Analytics\Enums;

enum HcmMetricCategory: string
{
    case WORKFORCE = 'workforce';
    case TURNOVER = 'turnover';
    case RECRUITMENT = 'recruitment';
    case ONBOARDING = 'onboarding';
    case ATTENDANCE = 'attendance';
    case LEAVE = 'leave';
    case PAYROLL = 'payroll';
    case COMPENSATION = 'compensation';
    case BENEFITS = 'benefits';
    case EXPENSES = 'expenses';
    case PERFORMANCE = 'performance';
    case TALENT = 'talent';
    case ENGAGEMENT = 'engagement';
    case EMPLOYEE_RELATIONS = 'employee_relations';
    case HR_SERVICE = 'hr_service';
}
