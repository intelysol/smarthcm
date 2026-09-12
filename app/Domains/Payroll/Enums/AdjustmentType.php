<?php

namespace App\Domains\Payroll\Enums;

enum AdjustmentType: string
{
    case EARNING = 'earning';
    case DEDUCTION = 'deduction';
    case ARREAR = 'arrear';
    case REIMBURSEMENT = 'reimbursement';
}
