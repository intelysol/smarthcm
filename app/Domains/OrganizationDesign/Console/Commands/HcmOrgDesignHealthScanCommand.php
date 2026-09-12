<?php

namespace App\Domains\OrganizationDesign\Console\Commands;

use App\Domains\OrganizationDesign\Services\ArchitectureHealthAndGovernanceService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Console\Command;

class HcmOrgDesignHealthScanCommand extends Command
{
    protected $signature = 'hcm:org-design-health-scan {tenant_id?}';
    protected $description = 'Scan job architecture and organizational structures for health issues and anomalies';

    public function handle(ArchitectureHealthAndGovernanceService $governanceService): int
    {
        $tenantId = $this->argument('tenant_id');

        $tenants = $tenantId
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::all();

        $this->info("Starting Organizational Design health scan across {$tenants->count()} tenant(s)...");

        $totalIssues = 0;
        foreach ($tenants as $tenant) {
            $this->line("Scanning tenant {$tenant->name} ({$tenant->id})...");
            $result = $governanceService->scanHealth($tenant->id);
            $count = $result['total_issues_found'];
            $totalIssues += $count;
            $this->info(" -> Found {$count} issue(s).");
        }

        $this->info("Health scan complete. Total anomalies detected: {$totalIssues}");

        return self::SUCCESS;
    }
}
