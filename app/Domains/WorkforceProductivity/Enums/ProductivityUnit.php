<?php

namespace App\Domains\WorkforceProductivity\Enums;

enum ProductivityUnit: string
{
    case UNITS_PER_HOUR = 'units_per_hour';
    case CASES_PER_HOUR = 'cases_per_hour';
    case TICKETS_PER_HOUR = 'tickets_per_hour';
    case TRANSACTIONS_PER_HOUR = 'transactions_per_hour';
    case REVENUE_PER_HOUR = 'revenue_per_hour';
    case UNITS_PER_FTE = 'units_per_fte';
    case COST_PER_UNIT = 'cost_per_unit';
    case PERCENTAGE = 'percentage';
    case RATIO = 'ratio';
}
