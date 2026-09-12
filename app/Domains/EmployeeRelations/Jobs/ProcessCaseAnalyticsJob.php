<?php

namespace App\Domains\EmployeeRelations\Jobs;

use App\Domains\EmployeeRelations\Services\EmployeeRelationAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCaseAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(EmployeeRelationAnalyticsService $analyticsService): void
    {
        $analyticsService->generateMetrics($this->tenantId);
    }
}
