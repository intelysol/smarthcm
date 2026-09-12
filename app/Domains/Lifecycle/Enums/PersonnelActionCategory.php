<?php

namespace App\Domains\Lifecycle\Enums;

enum PersonnelActionCategory: string
{
    case PROMOTION = 'promotion';
    case TRANSFER = 'transfer';
    case JOB_CHANGE = 'job_change';
    case COMPENSATION = 'compensation';
    case ASSIGNMENT = 'assignment';
    case PROBATION = 'probation';
    case GENERAL = 'general';
}
