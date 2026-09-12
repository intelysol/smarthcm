<?php

namespace App\Domains\WorkforceCost\Enums;

enum CostComponentType: string
{
    case BASE_PAY = 'BASE_PAY';
    case OVERTIME = 'OVERTIME';
    case PREMIUM_PAY = 'PREMIUM_PAY';
    case BONUS = 'BONUS';
    case INCENTIVE = 'INCENTIVE';
    case ALLOWANCE = 'ALLOWANCE';
    case EMPLOYER_TAX = 'EMPLOYER_TAX';
    case EMPLOYER_CONTRIBUTION = 'EMPLOYER_CONTRIBUTION';
    case BENEFITS = 'BENEFITS';
    case INSURANCE = 'INSURANCE';
    case RETIREMENT = 'RETIREMENT';
    case EXPENSE = 'EXPENSE';
    case TRAVEL = 'TRAVEL';
    case TRAINING = 'TRAINING';
    case RECRUITMENT = 'RECRUITMENT';
    case CONTRACTOR = 'CONTRACTOR';
    case AGENCY = 'AGENCY';
    case ABSENCE = 'ABSENCE';
    case VACANCY = 'VACANCY';
    case OTHER = 'OTHER';
}