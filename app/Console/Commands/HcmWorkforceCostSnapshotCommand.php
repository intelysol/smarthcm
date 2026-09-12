<?php

namespace App\Console\Commands;

use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HcmWorkforceCostSnapshotCommand extends Command
{
    protected $signature = 'hcm:cost-snapshot {--tenant-id= : Tenant UUID} {--period-name= : e.g. 2026-10} {--start-date= : YYYY-MM-DD} {--end-date= : YYYY-MM-DD}';
    protected $description = 'Generate an immutable versioned workforce cost snapshot for a period';

    public function handle(LaborCostAggregationService $service): int
    {
        $tenantId = $this->option('tenant-id');
        $periodName = $this->option('period-name') ?? now()->format('Y-m');
        $startDate = Carbon::parse($this->option('start-date') ?? now()->startOfMonth()->toDateString());
        $endDate = Carbon::parse($this->option('end-date') ?? now()->endOfMonth()->toDateString());

        if (! $tenantId) {
            $this->error('The --tenant-id option is required.');
            return self::FAILURE;
        }

        $this->info("Building workforce cost snapshot for period {$periodName}...");
        $snapshot = $service->buildSnapshot($tenantId, $periodName, $startDate, $endDate);

        $this->info("Snapshot created successfully: {$snapshot->snapshot_number} (Total Cost: {$snapshot->total_workforce_cost} {$snapshot->currency})");
        return self::SUCCESS;
    }
}