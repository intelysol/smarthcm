<?php

namespace App\Console\Commands;

use App\Domains\WorkforceProductivity\Services\ProductivitySnapshotService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HcmGenerateProductivitySnapshotCommand extends Command
{
    protected $signature = 'hcm:productivity-snapshot {--tenant-id= : Tenant UUID} {--period-type=monthly : Period Type} {--period-name= : Period Name (e.g. 2026-10)} {--start-date= : YYYY-MM-DD} {--end-date= : YYYY-MM-DD}';
    protected $description = 'Generate an immutable workforce productivity snapshot for a period';

    public function handle(ProductivitySnapshotService $service): int
    {
        $tenantId = $this->option('tenant-id');
        $periodType = $this->option('period-type') ?? 'monthly';
        $periodName = $this->option('period-name') ?? now()->format('Y-m');
        $startDate = $this->option('start-date') ?? now()->startOfMonth()->toDateString();
        $endDate = $this->option('end-date') ?? now()->endOfMonth()->toDateString();

        if (! $tenantId) {
            $this->error('The --tenant-id option is required.');
            return self::FAILURE;
        }

        $this->info("Building productivity snapshot for period {$periodName}...");
        $snapshot = $service->buildSnapshot($tenantId, $periodType, $periodName, $startDate, $endDate);

        $this->info("Productivity snapshot created: {$snapshot->snapshot_number} (Output: {$snapshot->total_output}, Hours: {$snapshot->total_productive_hours})");
        return self::SUCCESS;
    }
}
