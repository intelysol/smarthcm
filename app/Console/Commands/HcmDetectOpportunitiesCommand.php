<?php

namespace App\Console\Commands;

use App\Domains\WorkforceOptimization\Services\WorkforceOpportunityService;
use Illuminate\Console\Command;

class HcmDetectOpportunitiesCommand extends Command
{
    protected $signature = 'hcm:detect-opportunities 
                            {--tenant= : Tenant ID to detect opportunities for}';

    protected $description = 'Scan organizational capacity, skills, overtime, and bottlenecks to identify workforce opportunities';

    public function handle(WorkforceOpportunityService $opportunityService): int
    {
        $tenantId = $this->option('tenant');
        $this->info("Scanning workforce data for opportunities...");

        $opportunities = $opportunityService->detectOpportunities($tenantId);

        $this->info("Detected " . count($opportunities) . " workforce opportunities.");
        $rows = [];
        foreach ($opportunities as $opp) {
            $rows[] = [
                $opp->opportunity_code,
                $opp->category,
                $opp->severity,
                $opp->title,
                $opp->estimated_hours_gap,
            ];
        }

        $this->table(['Code', 'Category', 'Severity', 'Title', 'Hours Gap'], $rows);

        return Command::SUCCESS;
    }
}
