<?php

namespace App\Domains\Payroll\Enums;

enum PaymentBatchStatus: string
{
    case DRAFT = 'draft';
    case GENERATED = 'generated';
    case SUBMITTED = 'submitted';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case FAILED = 'failed';
    case RECONCILED = 'reconciled';
}
