<?php

namespace App\Domains\Benefits\Enums;

enum LoanRestructureType: string
{
    case EXTEND_TENURE = 'extend_tenure';
    case MODIFY_INSTALLMENT = 'modify_installment';
    case PAYMENT_HOLIDAY = 'payment_holiday';
    case RATE_REDUCTION = 'rate_reduction';
}
