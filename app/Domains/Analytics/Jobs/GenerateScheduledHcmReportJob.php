<?php

namespace App\Domains\Analytics\Jobs;

use App\Domains\Analytics\Services\HcmReportBuilderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateScheduledHcmReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $dataset,
        public array $config
    ) {}

    public function handle(HcmReportBuilderService $reportService): void
    {
        $reportService->executeReportQuery($this->tenantId, $this->dataset, $this->config);
    }
}
