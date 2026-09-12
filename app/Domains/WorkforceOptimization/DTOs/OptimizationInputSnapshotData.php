<?php

namespace App\Domains\WorkforceOptimization\DTOs;

class OptimizationInputSnapshotData
{
    public function __construct(
        public readonly string $tenantId,
        public readonly ?string $runId = null,
        public readonly ?string $snapshotDate = null,
        public readonly array $opportunities = [],
        public readonly array $availableEmployees = [],
        public readonly array $capacityMetrics = [],
        public readonly array $costMetrics = [],
        public readonly array $productivityMetrics = [],
        public readonly array $departmentCapacity = [],
        public readonly array $skillShortages = [],
        public readonly array $overtimeSpikes = [],
        public readonly array $vacancies = [],
        public readonly array $costBaselines = [],
        public readonly array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'run_id' => $this->runId,
            'snapshot_date' => $this->snapshotDate ?? now()->toDateString(),
            'opportunities' => $this->opportunities,
            'available_employees' => $this->availableEmployees,
            'capacity_metrics' => $this->capacityMetrics,
            'cost_metrics' => $this->costMetrics,
            'productivity_metrics' => $this->productivityMetrics,
            'department_capacity' => $this->departmentCapacity,
            'skill_shortages' => $this->skillShortages,
            'overtime_spikes' => $this->overtimeSpikes,
            'vacancies' => $this->vacancies,
            'cost_baselines' => $this->costBaselines,
            'metadata' => $this->metadata,
        ];
    }
}
