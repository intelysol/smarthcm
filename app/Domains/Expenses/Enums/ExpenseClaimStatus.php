<?php

namespace App\Domains\Expenses\Enums;

enum ExpenseClaimStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case RETURNED = 'returned';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
    case FINANCE_REVIEW = 'finance_review';
    case READY_FOR_PAYMENT = 'ready_for_payment';
    case PAID = 'paid';
    case CLOSED = 'closed';
}
