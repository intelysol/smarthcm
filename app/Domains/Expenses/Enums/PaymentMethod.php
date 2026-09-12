<?php

namespace App\Domains\Expenses\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case PERSONAL_CARD = 'personal_card';
    case CORPORATE_CARD = 'corporate_card';
    case BANK_TRANSFER = 'bank_transfer';
    case PAYROLL = 'payroll';
    case OTHER = 'other';
}
