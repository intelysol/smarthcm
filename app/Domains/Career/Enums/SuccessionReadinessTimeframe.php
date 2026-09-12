<?php

namespace App\Domains\Career\Enums;

enum SuccessionReadinessTimeframe: string
{
    case ReadyNow = 'ready_now';
    case ReadyUnder1Year = 'ready_under_1_year';
    case Ready1To2Years = 'ready_1_to_2_years';
    case Ready2To3Years = 'ready_2_to_3_years';
    case LongTerm = 'long_term';
}
