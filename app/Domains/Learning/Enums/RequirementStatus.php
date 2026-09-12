<?php

namespace App\Domains\Learning\Enums;

enum RequirementStatus: string
{
    case REQUIRED = 'required';
    case ASSIGNED = 'assigned';
    case ENROLLED = 'enrolled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case OVERDUE = 'overdue';
    case WAIVED = 'waived';
    case EXEMPTED = 'exempted';
}
