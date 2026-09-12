<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitCoverage;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BenefitCoverageService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function createCoverage(BenefitPlan $plan, array $data, ?User $actor = null): BenefitCoverage
    {
        $coverage = BenefitCoverage::create([
            'tenant_id' => $plan->tenant_id,
            'benefit_plan_id' => $plan->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'coverage_multiplier' => $data['coverage_multiplier'] ?? 1.0000,
            'employee_cost_factor' => $data['employee_cost_factor'] ?? 1.0000,
            'employer_cost_factor' => $data['employer_cost_factor'] ?? 1.0000,
            'fixed_employee_cost' => $data['fixed_employee_cost'] ?? null,
            'fixed_employer_cost' => $data['fixed_employer_cost'] ?? null,
            'max_dependents' => $data['max_dependents'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->auditService->record(
            tenantId: $plan->tenant_id,
            eventType: 'benefit_coverage.created',
            action: 'create',
            entityType: BenefitCoverage::class,
            entityId: $coverage->id,
            actorId: $actor?->id,
            after: $coverage->toArray()
        );

        return $coverage;
    }

    public function calculateEstimatedCosts(BenefitPlan $plan, ?BenefitCoverage $coverage = null): array
    {
        $baseEmployeeCost = (float) $plan->employee_cost;
        $baseEmployerCost = (float) $plan->employer_cost;

        if (! $coverage) {
            return [
                'employee_cost' => $baseEmployeeCost,
                'employer_cost' => $baseEmployerCost,
                'total_cost' => $baseEmployeeCost + $baseEmployerCost,
            ];
        }

        $empCost = $coverage->fixed_employee_cost !== null
            ? (float) $coverage->fixed_employee_cost
            : $baseEmployeeCost * (float) $coverage->employee_cost_factor;

        $emprCost = $coverage->fixed_employer_cost !== null
            ? (float) $coverage->fixed_employer_cost
            : $baseEmployerCost * (float) $coverage->employer_cost_factor;

        return [
            'employee_cost' => round($empCost, 4),
            'employer_cost' => round($emprCost, 4),
            'total_cost' => round($empCost + $emprCost, 4),
        ];
    }

    public function getCoveragesForPlan(BenefitPlan $plan): Collection
    {
        return $plan->coverages()->where('is_active', true)->get();
    }
}
