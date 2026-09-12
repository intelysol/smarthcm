<?php

namespace App\Domains\WorkforceAdmin\Events;

use App\Domains\WorkforceAdmin\Models\OpsBulkOperation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HrBulkOperationExecuted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public OpsBulkOperation $bulkOperation
    ) {}
}
