<?php

namespace App\Domains\Benefits\Enums;

enum RetirementPlanType: string
{
    case PROVIDENT_FUND = 'provident_fund';
    case PENSION = 'pension';
    case DEFINED_CONTRIBUTION = 'defined_contribution';
    case RETIREMENT_SAVINGS = 'retirement_savings';
    case GRATUITY = 'gratuity';
}
