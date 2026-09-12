<?php

namespace App\Domains\SelfService\Console\Commands;

use App\Domains\SelfService\Jobs\ProcessServiceSlaEscalationsJob;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Console\Command;

class HcmSharedServicesMonitorCommand extends Command
{
    protected $signature = 'hcm:shared-services-monitor {--tenant= : Specific tenant ID to monitor}';
    protected $description = 'Run periodic background monitoring, SLA clock evaluations, and escalations for HR Shared Services 2.0';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        $tenants = $tenantId
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->info('No tenants found for shared services monitoring.');
            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->info("Running HR Shared Services Monitor for Tenant: {$tenant->id}");

            // 1. Process SLA escalations & breaches
            ProcessServiceSlaEscalationsJob::dispatchSync($tenant->id);
            $this->line(" - Processed SLA clock checks & escalations.");
        }

        $this->info('HR Shared Services 2.0 background monitoring completed successfully.');
        return 0;
    }
}
