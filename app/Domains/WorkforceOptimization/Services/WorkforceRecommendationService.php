<?php

namespace App\Domains\WorkforceOptimization\Services;

use App\Domains\WorkforceOptimization\Contracts\OptimizationSolverInterface;
use App\Domains\WorkforceOptimization\DTOs\OptimizationInputSnapshotData;
use App\Domains\WorkforceOptimization\DTOs\OptimizationRecommendationData;
use App\Domains\WorkforceOptimization\Enums\RecommendationStatus;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationModelVersion;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOpportunity;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendationFactor;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRun;
use Illuminate\Support\Collection;

class WorkforceRecommendationService
{
    public function __construct(
        protected OptimizationSolverInterface $solver
    ) {}

    /**
     * Generate actionable recommendations for a given optimization run.
     *
     * @param HcmWorkforceOptimizationRun $run
     * @param array $options
     * @return Collection
     */
    public function generateRecommendations(
        HcmWorkforceOptimizationRun $run,
        array $options = []
    ): Collection {
        // Collect opportunities for the tenant / scope
        $oppsQuery = HcmWorkforceOptimizationOpportunity::whereIn('status', ['open', 'OPEN']);
        if ($run->tenant_id) {
            $oppsQuery->where('tenant_id', $run->tenant_id);
        }

        $opportunities = $oppsQuery->get()->map(fn($o) => [
            'id' => $o->id,
            'opportunity_code' => $o->opportunity_code,
            'category' => $o->category,
            'title' => $o->title,
            'department_id' => $o->department_id,
            'business_unit_id' => $o->business_unit_id ?? null,
            'job_role_id' => $o->details['job_role_id'] ?? null,
            'skill_id' => $o->details['skill_id'] ?? null,
            'employee_id' => $o->details['employee_id'] ?? null,
            'severity' => $o->severity,
            'estimated_hours_gap' => (float) ($o->capacity_gap_hours ?: ($o->estimated_hours_gap ?? 40.0)),
            'estimated_cost_impact' => (float) ($o->estimated_impact_amount ?: ($o->estimated_cost_impact ?? 1500.0)),
            'estimated_productivity_impact_pct' => (float) ($o->details['productivity_impact_pct'] ?? ($o->estimated_productivity_impact_pct ?? 10.0)),
            'metrics' => $o->details ?? [],
        ])->toArray();

        $snapshot = new OptimizationInputSnapshotData(
            runId: $run->id,
            tenantId: $run->tenant_id,
            opportunities: $opportunities,
            availableEmployees: [],
            capacityMetrics: [],
            costMetrics: [],
            productivityMetrics: []
        );

        $modelVersion = $run->modelVersion;
        $recommendationCandidates = $this->solver->solve($snapshot, $modelVersion, $options);

        $savedRecommendations = collect();

        foreach ($recommendationCandidates as $dto) {
            /** @var OptimizationRecommendationData $dto */
            $recCode = 'REC_' . strtoupper(substr(uniqid(), -8));

            $rec = HcmWorkforceOptimizationRecommendation::create([
                'tenant_id' => $run->tenant_id,
                'opportunity_id' => $dto->opportunityId,
                'recommendation_code' => $recCode,
                'action_type' => $dto->actionType,
                'title' => $dto->title,
                'executive_summary' => $dto->executiveSummary ?? ($dto->description ?? $dto->title),
                'source_department_id' => $dto->sourceDepartmentId,
                'target_department_id' => $dto->targetDepartmentId,
                'candidate_employee_id' => $dto->candidateEmployeeId,
                'decision_score' => $dto->decisionScore,
                'cost_impact' => $dto->costImpact,
                'capacity_impact_hours' => $dto->capacityImpactHours,
                'productivity_impact_pct' => $dto->productivityImpactPct,
                'time_to_realize_days' => $dto->timeToRealizeDays,
                'risk_level' => $dto->riskLevel,
                'confidence' => $dto->confidence,
                'lifecycle_status' => RecommendationStatus::GENERATED->value,
                'authoritative_module' => $dto->authoritativeModule,
                'required_approval_role' => $dto->requiredApprovalRole,
            ]);

            // Save recommendation factors (explainability)
            $this->recordFactors($rec, $dto);

            $savedRecommendations->push($rec);
        }

        return $savedRecommendations;
    }

    private function recordFactors(
        HcmWorkforceOptimizationRecommendation $rec,
        OptimizationRecommendationData $dto
    ): void {
        $factors = [
            [
                'factor_type' => 'objective_alignment',
                'name' => 'Labor Cost Differential',
                'score' => 75.0,
                'weight' => 1.0,
                'details' => "Projected cost variance is \${$dto->costImpact}.",
            ],
            [
                'factor_type' => 'objective_alignment',
                'name' => 'Capacity Yield',
                'score' => 80.0,
                'weight' => 1.0,
                'details' => "Supplies {$dto->capacityImpactHours} productive hours.",
            ],
            [
                'factor_type' => 'objective_alignment',
                'name' => 'Productivity Acceleration',
                'score' => 85.0,
                'weight' => 1.0,
                'details' => "Expected +{$dto->productivityImpactPct}% throughput increase.",
            ],
            [
                'factor_type' => 'risk_indicator',
                'name' => 'Execution Risk',
                'score' => 100 - $dto->riskImpactScore,
                'weight' => 0.5,
                'details' => "Implementation risk assessed at {$dto->riskImpactScore}/100.",
            ],
        ];

        foreach ($factors as $factor) {
            HcmWorkforceOptimizationRecommendationFactor::create(array_merge([
                'tenant_id' => $rec->tenant_id,
                'recommendation_id' => $rec->id,
            ], $factor));
        }
    }
}
