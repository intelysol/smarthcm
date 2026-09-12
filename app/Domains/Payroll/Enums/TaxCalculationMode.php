<?php

namespace App\Domains\Payroll\Enums;

enum TaxCalculationMode: string
{
    case PROGRESSIVE_BRACKETS = 'progressive_brackets';
    case FLAT_RATE = 'flat_rate';
    case ANNUALIZED = 'annualized';
    case EXEMPT = 'exempt';
}
