<?php

namespace App\Domains\WorkforceAdmin\Enums;

enum BulkOperationStatus: string
{
    case DRAFT = 'draft';
    case VALIDATING = 'validating';
    case VALIDATED = 'validated';
    case DRY_RUN_READY = 'dry_run_ready';
    case APPROVED = 'approved';
    case EXECUTING = 'executing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
