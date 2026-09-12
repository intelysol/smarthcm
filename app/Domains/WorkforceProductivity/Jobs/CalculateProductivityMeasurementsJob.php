<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\DTOs\ProductivityMeasurementData;
use App\Domains\WorkforceProductivity\Events\ProductivityMeasurementCalculated;
use App\Domains\WorkforceProductivity\Services\ProductivityMeasurementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateProductivityMeasurementsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly array $measurementDataArray,
        public readonly ?int $userId = null
    ) {}

    public function handle(ProductivityMeasurementService $service): void
    {
        $dto = new ProductivityMeasurementData(
            tenantId: $this->measurementDataArray['tenant_id'],
            metricDefinitionId: $this->measurementDataArray['metric_definition_id'],
            metricVersionId: $this->measurementDataArray['metric_version_id'] ?? null,
            departmentId: $this->measurementDataArray['department_id'] ?? null,
            locationId: $this->measurementDataArray['location_id'] ?? null,
            shiftId: $this->measurementDataArray['shift_id'] ?? null,
            employeeId: $this->measurementDataArray['employee_id'] ?? null,
            periodType: $this->measurementDataArray['period_type'] ?? 'monthly',
            periodName: $this->measurementDataArray['period_name'],
            periodStart: $this->measurementDataArray['period_start'],
            periodEnd: $this->measurementDataArray['period_end'],
            outputVolume: (float) $this->measurementDataArray['output_volume'],
            laborHours: (float) $this->measurementDataArray['labor_hours'],
            productiveHours: (float) $this->measurementDataArray['productive_hours'],
            scheduledHours: (float) ($this->measurementDataArray['scheduled_hours'] ?? 0.0),
            availableHours: (float) ($this->measurementDataArray['available_hours'] ?? 0.0),
            overtimeHours: (float) ($this->measurementDataArray['overtime_hours'] ?? 0.0),
            idleHours: (float) ($this->measurementDataArray['idle_hours'] ?? 0.0),
            qualityRate: isset($this->measurementDataArray['quality_rate']) ? (float) $this->measurementDataArray['quality_rate'] : null,
            laborCost: (float) ($this->measurementDataArray['labor_cost'] ?? 0.0),
            idempotencyKey: $this->measurementDataArray['idempotency_key'] ?? null
        );

        $measurement = $service->calculateMeasurement($dto, $this->userId);
        event(new ProductivityMeasurementCalculated($measurement));
    }
}
