<?php

namespace App\Domains\EmployeeDocuments\Jobs;

use App\Domains\EmployeeDocuments\Models\EmployeeDocumentBulkBatch;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentBulkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBulkEmployeeDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $batchId)
    {
    }

    public function handle(EmployeeDocumentBulkService $service): void
    {
        $batch = EmployeeDocumentBulkBatch::findOrFail($this->batchId);
        $service->processBatch($batch);
    }
}
