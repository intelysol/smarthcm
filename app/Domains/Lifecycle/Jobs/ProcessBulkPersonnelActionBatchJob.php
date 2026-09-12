<?php

namespace App\Domains\Lifecycle\Jobs;

use App\Domains\Lifecycle\Models\PersonnelActionBulkBatch;
use App\Domains\Lifecycle\Services\PersonnelActionBulkService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBulkPersonnelActionBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $batchId, protected int $userId)
    {
    }

    public function handle(PersonnelActionBulkService $bulkService): void
    {
        $batch = PersonnelActionBulkBatch::findOrFail($this->batchId);
        $user = User::findOrFail($this->userId);

        $bulkService->executeBatch($batch, $user);
    }
}
