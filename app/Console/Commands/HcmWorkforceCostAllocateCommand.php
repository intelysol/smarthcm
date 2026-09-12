<?php

namespace App\Console\Commands;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use App\Domains\WorkforceCost\Services\LaborCostAllocationService;
use Illuminate\Console\Command;

class HcmWorkforceCostAllocateCommand extends Command
{
    protected $signature = 'hcm:cost-allocate {--tenant-id= : Tenant UUID} {--limit=100 : Batch limit}';
    protected $description = 'Execute labor cost allocation for unallocated cost lines';

    public function handle(LaborCostAllocationService $service): int
    {
        $tenantId = $this->option('tenant-id');
        $limit = (int) $this->option('limit');

        if (! $tenantId) {
            $this->error('The --tenant-id option is required.');
            return self::FAILURE;
        }

        $lines = HcmWorkforceCostLine::where('tenant_id', $tenantId)
            ->whereDoesntHave('allocations')
            ->limit($limit)
            ->get();

        $this->info("Allocating {$lines->count()} cost lines...");
        $count = 0;
        foreach ($lines as $line) {
            $service->allocateCostLine($line);
            $count++;
        }

        $this->info("Successfully allocated {$count} cost lines.");
        return self::SUCCESS;
    }
}