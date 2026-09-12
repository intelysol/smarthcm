<?php

namespace App\Console\Commands;

use App\Domains\Analytics\Services\HcmWorkforceSnapshotService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Console\Command;

class HcmAnalyticsSnapshotCommand extends Command
{
    protected $signature = 'hcm:analytics-snapshot {--tenant= : Specific Tenant UUID} {--date= : Target Date YYYY-MM-DD} {--type=daily : Snapshot type (daily, monthly)}';

    protected $description = 'Generate daily or monthly workforce analytics snapshots for active tenants';

    public function handle(HcmWorkforceSnapshotService $snapshotService): int
    {
        $tenantId = $this->option('tenant');
        $date = $this->option('date') ?? now()->toDateString();
        $type = $this->option('type') ?? 'daily';

        $this->info("Starting HCM Workforce Snapshot generation for date: {$date} ({$type})...");

        $tenants = $tenantId ? Tenant::where('id', $tenantId)->get() : Tenant::all();

        foreach ($tenants as $t) {
            $this->info("Processing tenant {$t->name} ({$t->id})...");
            $snapshot = $snapshotService->buildSnapshot($t->id, $date, $type);
            $this->info("Successfully created snapshot. Headcount: {$snapshot->headcount_total} (Active: {$snapshot->headcount_active}, FTE: {$snapshot->fte_total})");
        }

        $this->info("Workforce snapshot generation complete.");

        return 0;
    }
}
