<?php

namespace App\Domains\Expenses\Enums;

enum ReimbursementMethod: string
{
    case PAYROLL = 'payroll';
    case BANK_PAYMENT = 'bank_payment';
    case ACCOUNTS_PAYABLE = 'accounts_payable';
    case CASH = 'cash';
    case OTHER = 'other';
}
