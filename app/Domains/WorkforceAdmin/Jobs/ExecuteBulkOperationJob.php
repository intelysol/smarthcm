<?php

namespace App\Domains\WorkforceAdmin\Jobs;

use App\Domains\WorkforceAdmin\Models\OpsBulkOperation;
use App\Domains\WorkforceAdmin\Services\BulkOperationService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteBulkOperationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public OpsBulkOperation $bulkOperation,
        public ?User $actor = null
    ) {}

    public function handle(BulkOperationService $bulkService): void
    {
        $bulkService->executeBulkOperation($this->bulkOperation, $this->actor);
    }
}
