<?php

namespace App\Domains\Lifecycle\Enums;

enum BulkBatchStatus: string
{
    case DRAFT = 'draft';
    case VALIDATING = 'validating';
    case VALIDATED = 'validated';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
