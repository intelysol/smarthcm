<?php

namespace App\Console\Commands;

use App\Domains\WorkforceProductivity\DTOs\ProductivityMeasurementData;
use App\Domains\WorkforceProductivity\Services\ProductivityMeasurementService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HcmCalculateProductivityCommand extends Command
{
    protected $signature = 'hcm:productivity-calculate {--tenant-id= : Tenant UUID} {--metric-id= : Metric Definition UUID} {--output= : Total output} {--hours= : Productive hours} {--cost=0 : Labor cost} {--period-name= : e.g. 2026-10}';
    protected $description = 'Calculate workforce productivity measurement for a metric and period';

    public function handle(ProductivityMeasurementService $service): int
    {
        $tenantId = $this->option('tenant-id');
        $metricId = $this->option('metric-id');
        $output = (float) $this->option('output');
        $hours = (float) $this->option('hours');
        $cost = (float) $this->option('cost');
        $periodName = $this->option('period-name') ?? now()->format('Y-m');

        if (! $tenantId || ! $metricId) {
            $this->error('Both --tenant-id and --metric-id options are required.');
            return self::FAILURE;
        }

        $now = now();
        $dto = new ProductivityMeasurementData(
            tenantId: $tenantId,
            metricDefinitionId: $metricId,
            metricVersionId: null,
            departmentId: null,
            locationId: null,
            shiftId: null,
            employeeId: null,
            periodType: 'monthly',
            periodName: $periodName,
            periodStart: $now->startOfMonth()->toDateString(),
            periodEnd: $now->endOfMonth()->toDateString(),
            outputVolume: $output,
            laborHours: $hours,
            productiveHours: $hours,
            laborCost: $cost
        );

        $measurement = $service->calculateMeasurement($dto);

        $this->info("Measurement calculated successfully! Rate: " . ($measurement->productivity_rate ?? 'N/A') . " (Cost/Unit: " . ($measurement->cost_per_unit ?? 'N/A') . ")");
        return self::SUCCESS;
    }
}
