<?php

namespace App\Domains\Learning\Jobs;

use App\Domains\Learning\Services\LearningReportService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLearningReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $tenantId, public readonly string $reportType) {}

    public function handle(LearningReportService $reportService, TenantContext $tenantContext): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        $tenantContext->set($tenant);

        match ($this->reportType) {
            'completion' => $reportService->completionReport($this->tenantId),
            'compliance' => $reportService->complianceReport($this->tenantId),
            'certifications' => $reportService->certificationReport($this->tenantId),
            'cost' => $reportService->costReport($this->tenantId),
            default => null,
        };
    }
}
