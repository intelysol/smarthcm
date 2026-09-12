<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\Services\ProductivityExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportProductivityReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $filePath
    ) {}

    public function handle(ProductivityExportService $exportService): void
    {
        $csvContent = $exportService->exportMeasurementsCsv(
            $this->tenantId,
            $this->startDate,
            $this->endDate
        );

        Storage::disk('local')->put($this->filePath, $csvContent);
    }
}
