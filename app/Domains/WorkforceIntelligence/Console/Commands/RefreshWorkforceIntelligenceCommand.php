<?php

namespace App\Domains\WorkforceIntelligence\Console\Commands;

use App\Domains\WorkforceIntelligence\Contracts\KpiOrchestrationInterface;
use App\Domains\WorkforceIntelligence\Contracts\WorkforceCommandCenterInterface;
use App\Domains\WorkforceIntelligence\Services\WorkforceRiskAggregationService;
use App\Models\Tenant;
use Illuminate\Console\Command;

class RefreshWorkforceIntelligenceCommand extends Command
{
    protected $signature = 'hcm:workforce-intelligence:refresh {--tenant= : Specific tenant UUID} {--period= : Period key YYYY-MM}';

    protected $description = 'Refresh executive metrics, composite health scores, and cross-domain risks';

    public function handle(
        KpiOrchestrationInterface $kpiService,
        WorkforceCommandCenterInterface $commandCenter,
        WorkforceRiskAggregationService $riskService
    ): int {
        $tenantId = $this->option('tenant');
        $periodKey = $this->option('period') ?? now()->format('Y-m');

        $tenants = $tenantId ? Tenant::where('id', $tenantId)->get() : Tenant::all();

        $this->info("Refreshing Workforce Intelligence Command Center for " . $tenants->count() . " tenants (Period: {$periodKey})...");

        foreach ($tenants as $tenant) {
            $this->line("Processing tenant: {$tenant->name} ({$tenant->id})");
            $kpiService->refreshAllKpis($tenant->id, $periodKey);
            $commandCenter->getExecutiveScorecard($tenant->id, null, $periodKey);
            $riskService->detectAndAggregateRisks($tenant->id);
        }

        $this->info("Workforce Intelligence refresh complete.");
        return self::SUCCESS;
    }
}
