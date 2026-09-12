<?php

namespace App\Domains\WorkforceOptimization\DTOs;

class OptimizationRecommendationData
{
    public function __construct(
        public readonly ?string $opportunityId = null,
        public readonly string $actionType = 'HIRE',
        public readonly string $title = 'Recommendation',
        public readonly ?string $executiveSummary = null,
        public readonly ?string $sourceDepartmentId = null,
        public readonly ?string $targetDepartmentId = null,
        public readonly ?string $candidateEmployeeId = null,
        public readonly float $decisionScore = 85.0,
        public readonly float $costImpact = 0.0,
        public readonly float $capacityImpactHours = 0.0,
        public readonly float $productivityImpactPct = 0.0,
        public readonly int $timeToRealizeDays = 30,
        public readonly string $riskLevel = 'low',
        public readonly string $confidence = 'HIGH',
        public readonly string $authoritativeModule = 'Core HR',
        public readonly string $requiredApprovalRole = 'HR Director',
        public readonly array $factors = [],
        public readonly ?string $description = null,
        public readonly ?string $targetBusinessUnitId = null,
        public readonly ?string $targetEmployeeId = null,
        public readonly ?string $targetSkillId = null,
        public readonly array $proposedParameters = [],
        public readonly float $riskImpactScore = 20.0,
        public readonly float $feasibilityScore = 85.0,
        public readonly string $priority = 'HIGH',
        public readonly ?string $tradeOffExplanation = null,
        public readonly ?string $rationaleNarrative = null
    ) {}

    public function toArray(): array
    {
        return [
            'opportunity_id' => $this->opportunityId,
            'action_type' => $this->actionType,
            'title' => $this->title,
            'executive_summary' => $this->executiveSummary,
            'source_department_id' => $this->sourceDepartmentId,
            'target_department_id' => $this->targetDepartmentId,
            'candidate_employee_id' => $this->candidateEmployeeId,
            'decision_score' => $this->decisionScore,
            'cost_impact' => $this->costImpact,
            'capacity_impact_hours' => $this->capacityImpactHours,
            'productivity_impact_pct' => $this->productivityImpactPct,
            'time_to_realize_days' => $this->timeToRealizeDays,
            'risk_level' => $this->riskLevel,
            'confidence' => $this->confidence,
            'authoritative_module' => $this->authoritativeModule,
            'required_approval_role' => $this->requiredApprovalRole,
            'factors' => $this->factors,
        ];
    }
}
