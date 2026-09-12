<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;
use App\Domains\WorkforceProductivity\DTOs\ProductivityMeasurementData;
use App\Domains\WorkforceProductivity\Models\HcmProductivityAudit;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurementLine;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMetricDefinition;
use App\Domains\WorkforceProductivity\Models\HcmProductivityOutputRecord;
use App\Domains\WorkforceProductivity\Models\HcmProductivityTimeRecord;
use Illuminate\Support\Str;

class ProductivityMeasurementService
{
    public function __construct(
        protected ProductivityCalculationInterface $calculator,
        protected ProductivityDataQualityService $qualityService
    ) {}

    /**
     * Record an operational output event with provenance.
     */
    public function recordOutput(array $data): HcmProductivityOutputRecord
    {
        return HcmProductivityOutputRecord::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $data['tenant_id'],
            'output_date' => $data['output_date'],
            'metric_definition_id' => $data['metric_definition_id'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'cost_center_id' => $data['cost_center_id'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'shift_id' => $data['shift_id'] ?? null,
            'output_type' => $data['output_type'] ?? 'units',
            'units_completed' => $data['units_completed'] ?? 0,
            'units_defective' => $data['units_defective'] ?? 0,
            'rework_count' => $data['rework_count'] ?? 0,
            'revenue_generated' => $data['revenue_generated'] ?? 0,
            'quality_score' => $data['quality_score'] ?? null,
            'source_domain' => $data['source_domain'] ?? 'operations',
            'source_record_id' => $data['source_record_id'] ?? null,
            'nature' => $data['nature'] ?? 'ACTUAL',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Record a productive time event with category classification.
     */
    public function recordTime(array $data): HcmProductivityTimeRecord
    {
        $minutes = (int) ($data['minutes'] ?? (isset($data['hours']) ? round($data['hours'] * 60) : 0));
        $hours = (float) ($data['hours'] ?? round($minutes / 60, 2));

        return HcmProductivityTimeRecord::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $data['tenant_id'],
            'record_date' => $data['record_date'],
            'employee_id' => $data['employee_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'shift_id' => $data['shift_id'] ?? null,
            'category' => $data['category'] ?? 'PRODUCTIVE',
            'nature' => $data['nature'] ?? 'ACTUAL',
            'minutes' => $minutes,
            'hours' => $hours,
            'source_domain' => $data['source_domain'] ?? 'attendance',
            'source_record_id' => $data['source_record_id'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Calculate and store an aggregate productivity measurement for a period and dimension.
     */
    public function calculateMeasurement(ProductivityMeasurementData $data, ?int $userId = null): HcmProductivityMeasurement
    {
        // Check idempotency if key provided
        if (! empty($data->idempotencyKey)) {
            $existing = HcmProductivityMeasurement::where('tenant_id', $data->tenantId)
                ->where('idempotency_key', $data->idempotencyKey)
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        // Validate data quality & zero-denominator condition
        $qualityStatus = $this->qualityService->evaluateQuality(
            $data->outputVolume,
            $data->productiveHours,
            $data->availableHours
        );

        $productivityRate = $this->calculator->calculateRate($data->outputVolume, $data->productiveHours);
        $utilizationRate = $this->calculator->calculateUtilization($data->productiveHours, $data->availableHours);
        $costPerUnit = $this->calculator->calculateUnitCost($data->laborCost, $data->outputVolume);
        $costPerProductiveHour = $this->calculator->calculateUnitCost($data->laborCost, $data->productiveHours);
        $outputPerDollar = $this->calculator->calculateOutputPerDollar($data->outputVolume, $data->laborCost);

        $measurement = HcmProductivityMeasurement::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $data->tenantId,
            'metric_definition_id' => $data->metricDefinitionId,
            'metric_version_id' => $data->metricVersionId,
            'department_id' => $data->departmentId,
            'location_id' => $data->locationId,
            'shift_id' => $data->shiftId,
            'employee_id' => $data->employeeId,
            'period_type' => $data->periodType,
            'period_name' => $data->periodName,
            'period_start' => $data->periodStart,
            'period_end' => $data->periodEnd,
            'output_volume' => $data->outputVolume,
            'labor_hours' => $data->laborHours,
            'productive_hours' => $data->productiveHours,
            'scheduled_hours' => $data->scheduledHours,
            'available_hours' => $data->availableHours,
            'overtime_hours' => $data->overtimeHours,
            'idle_hours' => $data->idleHours,
            'productivity_rate' => $productivityRate,
            'utilization_rate' => $utilizationRate,
            'quality_rate' => $data->qualityRate,
            'labor_cost' => $data->laborCost,
            'cost_per_unit' => $costPerUnit,
            'cost_per_productive_hour' => $costPerProductiveHour,
            'output_per_dollar' => $outputPerDollar,
            'data_quality_status' => $qualityStatus,
            'source_provenance' => $data->sourceProvenance,
            'idempotency_key' => $data->idempotencyKey,
        ]);

        HcmProductivityAudit::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $data->tenantId,
            'action' => 'CALCULATE_MEASUREMENT',
            'target_type' => HcmProductivityMeasurement::class,
            'target_id' => $measurement->id,
            'user_id' => $userId,
            'changes' => [
                'output' => $data->outputVolume,
                'rate' => $productivityRate,
                'unit_cost' => $costPerUnit,
                'quality' => $qualityStatus,
            ],
            'created_at' => now(),
        ]);

        return $measurement;
    }
}
