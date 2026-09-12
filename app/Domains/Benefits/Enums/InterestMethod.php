<?php

namespace App\Domains\Benefits\Enums;

enum InterestMethod: string
{
    case FLAT_RATE = 'flat_rate';
    case REDUCING_BALANCE = 'reducing_balance';
    case ZERO_INTEREST = 'zero_interest';
    case CUSTOM_RULE = 'custom_rule';
}
