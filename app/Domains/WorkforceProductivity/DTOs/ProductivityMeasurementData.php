<?php

namespace App\Domains\WorkforceProductivity\DTOs;

class ProductivityMeasurementData
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $metricDefinitionId,
        public readonly ?string $metricVersionId,
        public readonly ?string $departmentId,
        public readonly ?string $locationId,
        public readonly ?string $shiftId,
        public readonly ?string $employeeId,
        public readonly string $periodType,
        public readonly string $periodName,
        public readonly string $periodStart,
        public readonly string $periodEnd,
        public readonly float $outputVolume,
        public readonly float $laborHours,
        public readonly float $productiveHours,
        public readonly float $scheduledHours = 0.0,
        public readonly float $availableHours = 0.0,
        public readonly float $overtimeHours = 0.0,
        public readonly float $idleHours = 0.0,
        public readonly ?float $productivityRate = null,
        public readonly ?float $utilizationRate = null,
        public readonly ?float $qualityRate = null,
        public readonly float $laborCost = 0.0,
        public readonly ?float $costPerUnit = null,
        public readonly ?float $costPerProductiveHour = null,
        public readonly ?float $outputPerDollar = null,
        public readonly string $dataQualityStatus = 'VALID',
        public readonly ?array $sourceProvenance = null,
        public readonly ?string $idempotencyKey = null,
    ) {}

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'metric_definition_id' => $this->metricDefinitionId,
            'metric_version_id' => $this->metricVersionId,
            'department_id' => $this->departmentId,
            'location_id' => $this->locationId,
            'shift_id' => $this->shiftId,
            'employee_id' => $this->employeeId,
            'period_type' => $this->periodType,
            'period_name' => $this->periodName,
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
            'output_volume' => $this->outputVolume,
            'labor_hours' => $this->laborHours,
            'productive_hours' => $this->productiveHours,
            'scheduled_hours' => $this->scheduledHours,
            'available_hours' => $this->availableHours,
            'overtime_hours' => $this->overtimeHours,
            'idle_hours' => $this->idleHours,
            'productivity_rate' => $this->productivityRate,
            'utilization_rate' => $this->utilizationRate,
            'quality_rate' => $this->qualityRate,
            'labor_cost' => $this->laborCost,
            'cost_per_unit' => $this->costPerUnit,
            'cost_per_productive_hour' => $this->costPerProductiveHour,
            'output_per_dollar' => $this->outputPerDollar,
            'data_quality_status' => $this->dataQualityStatus,
            'source_provenance' => $this->sourceProvenance,
            'idempotency_key' => $this->idempotencyKey,
        ];
    }
}
