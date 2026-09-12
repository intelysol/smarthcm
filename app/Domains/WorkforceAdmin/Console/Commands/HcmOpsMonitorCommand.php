<?php

namespace App\Domains\WorkforceAdmin\Console\Commands;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Jobs\CheckSlaBreachesJob;
use App\Domains\WorkforceAdmin\Services\ConfigurationHealthService;
use App\Domains\WorkforceAdmin\Services\HrCalendarService;
use Illuminate\Console\Command;

class HcmOpsMonitorCommand extends Command
{
    protected $signature = 'hcm:ops-monitor {--tenant= : Specific tenant ID to monitor}';
    protected $description = 'Run periodic HCM Workforce Administration operational monitoring and SLA checks';

    public function handle(
        HrCalendarService $calendarService,
        ConfigurationHealthService $healthService
    ): int {
        $tenantId = $this->option('tenant');

        $tenants = $tenantId
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->info('No tenants found for monitoring.');
            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->info("Running HCM Ops Monitor for Tenant: {$tenant->id}");

            // 1. Dispatch SLA breach checks
            CheckSlaBreachesJob::dispatchSync($tenant->id);

            // 2. Sync calendar events
            $eventsCount = $calendarService->syncCalendarEvents($tenant->id);
            $this->line(" - Synced {$eventsCount} calendar events.");

            // 3. Run config health checks
            $healthChecks = $healthService->runDiagnostics($tenant->id);
            $this->line(" - Executed " . count($healthChecks) . " configuration health checks.");
        }

        $this->info('HCM Workforce Administration monitoring run completed successfully.');
        return 0;
    }
}
