<?php

namespace App\Domains\WorkforceOptimization\DTOs;

class WorkforceOpportunityData
{
    public function __construct(
        public readonly string $opportunityCode,
        public readonly string $title,
        public readonly string $category,
        public readonly string $severity,
        public readonly ?string $departmentId = null,
        public readonly ?string $roleOrSkill = null,
        public readonly float $estimatedImpactAmount = 0.0,
        public readonly float $capacityGapHours = 0.0,
        public readonly float $confidenceScore = 0.85,
        public readonly array $details = [],
        public readonly ?string $description = null,
        public readonly ?string $businessUnitId = null,
        public readonly ?string $jobRoleId = null,
        public readonly ?string $skillId = null,
        public readonly ?string $employeeId = null,
        public readonly float $estimatedHoursGap = 0.0,
        public readonly float $estimatedCostImpact = 0.0,
        public readonly float $estimatedProductivityImpactPct = 0.0,
        public readonly array $detectionMetrics = []
    ) {}

    public function toArray(): array
    {
        return [
            'opportunity_code' => $this->opportunityCode,
            'title' => $this->title,
            'category' => $this->category,
            'severity' => $this->severity,
            'department_id' => $this->departmentId,
            'role_or_skill' => $this->roleOrSkill,
            'estimated_impact_amount' => $this->estimatedImpactAmount,
            'capacity_gap_hours' => $this->capacityGapHours,
            'confidence_score' => $this->confidenceScore,
            'details' => $this->details,
        ];
    }
}
