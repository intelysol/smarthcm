<?php

namespace App\Domains\Payroll\Enums;

enum PaymentMethod: string
{
    case BANK_TRANSFER = 'bank_transfer';
    case DIRECT_DEPOSIT = 'direct_deposit';
    case CHEQUE = 'cheque';
    case CASH = 'cash';
}
