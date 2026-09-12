<?php

namespace App\Domains\OrganizationDesign\Jobs;

use App\Domains\OrganizationDesign\Services\ArchitectureHealthAndGovernanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanArchitectureHealthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(ArchitectureHealthAndGovernanceService $governanceService): void
    {
        $result = $governanceService->scanHealth($this->tenantId);
        Log::info("Completed architecture health scan for tenant {$this->tenantId}", [
            'total_issues' => $result['total_issues_found'],
        ]);
    }
}
