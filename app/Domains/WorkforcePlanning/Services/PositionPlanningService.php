<?php

namespace App\Domains\WorkforcePlanning\Services;

use App\Domains\WorkforcePlanning\Enums\PlanPositionStatus;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePositionBudget;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePositionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PositionPlanningService
{
    public function createPosition(HcmWorkforcePlan $plan, array $data): HcmWorkforcePositionPlan
    {
        if ($plan->status === 'locked') {
            throw ValidationException::withMessages(['plan' => 'Cannot add positions to a locked plan.']);
        }

        return DB::transaction(function () use ($plan, $data) {
            $position = HcmWorkforcePositionPlan::create([
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'position_code' => $data['position_code'],
                'title' => $data['title'],
                'department_id' => $data['department_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'job_grade_id' => $data['job_grade_id'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'cost_center_id' => $data['cost_center_id'] ?? null,
                'status' => $data['status'] ?? PlanPositionStatus::PLANNED->value,
                'is_budgeted' => $data['is_budgeted'] ?? true,
                'fte' => $data['fte'] ?? 1.00,
                'planned_start_date' => $data['planned_start_date'] ?? null,
                'planned_end_date' => $data['planned_end_date'] ?? null,
                'current_employee_id' => $data['current_employee_id'] ?? null,
                'actual_position_id' => $data['actual_position_id'] ?? null,
            ]);

            if (!empty($data['budget'])) {
                $this->assignBudget($position, $data['budget']);
            }

            return $position;
        });
    }

    public function assignBudget(HcmWorkforcePositionPlan $position, array $budgetData): HcmWorkforcePositionBudget
    {
        $baseSalary = (float) ($budgetData['base_salary_budget'] ?? 0.00);
        $bonus = (float) ($budgetData['bonus_budget'] ?? 0.00);
        $benefits = (float) ($budgetData['benefits_budget'] ?? 0.00);
        $employerContributions = (float) ($budgetData['employer_contributions_budget'] ?? 0.00);
        $payrollTax = (float) ($budgetData['payroll_tax_budget'] ?? 0.00);
        $recruitmentCost = (float) ($budgetData['recruitment_cost_budget'] ?? 0.00);
        $equipmentCost = (float) ($budgetData['equipment_cost_budget'] ?? 0.00);

        // Deterministic sum using bcmath / exact rounding
        $totalEmploymentCost = round($baseSalary + $bonus + $benefits + $employerContributions + $payrollTax + $recruitmentCost + $equipmentCost, 2);

        return HcmWorkforcePositionBudget::updateOrCreate(
            [
                'tenant_id' => $position->tenant_id,
                'position_plan_id' => $position->id,
            ],
            [
                'base_salary_budget' => $baseSalary,
                'bonus_budget' => $bonus,
                'benefits_budget' => $benefits,
                'employer_contributions_budget' => $employerContributions,
                'payroll_tax_budget' => $payrollTax,
                'recruitment_cost_budget' => $recruitmentCost,
                'equipment_cost_budget' => $equipmentCost,
                'total_employment_cost' => $totalEmploymentCost,
                'currency' => $budgetData['currency'] ?? 'USD',
            ]
        );
    }

    public function freezePosition(HcmWorkforcePositionPlan $position): HcmWorkforcePositionPlan
    {
        $position->update(['status' => PlanPositionStatus::FROZEN->value]);
        return $position;
    }

    public function unfreezePosition(HcmWorkforcePositionPlan $position): HcmWorkforcePositionPlan
    {
        $position->update(['status' => PlanPositionStatus::OPEN->value]);
        return $position;
    }

    public function eliminatePosition(HcmWorkforcePositionPlan $position, string $reason): HcmWorkforcePositionPlan
    {
        $position->update([
            'status' => PlanPositionStatus::ELIMINATED->value,
            'elimination_reason' => $reason,
        ]);
        return $position;
    }

    public function getPositionSummary(string $tenantId, string $planId): array
    {
        $positions = HcmWorkforcePositionPlan::where('tenant_id', $tenantId)
            ->where('plan_id', $planId)
            ->with('budget')
            ->get();

        $totalPositions = $positions->count();
        $budgetedPositions = $positions->where('is_budgeted', true)->count();
        $nonBudgetedPositions = $totalPositions - $budgetedPositions;
        $occupiedPositions = $positions->where('status', PlanPositionStatus::OCCUPIED->value)->count();
        $vacantPositions = $positions->whereIn('status', [PlanPositionStatus::PLANNED->value, PlanPositionStatus::BUDGETED->value, PlanPositionStatus::OPEN->value])->count();
        $frozenPositions = $positions->where('status', PlanPositionStatus::FROZEN->value)->count();
        $eliminatedPositions = $positions->where('status', PlanPositionStatus::ELIMINATED->value)->count();

        $totalBudgetCost = $positions->sum(fn ($p) => (float) ($p->budget->total_employment_cost ?? 0.00));

        return [
            'total_positions' => $totalPositions,
            'budgeted_positions' => $budgetedPositions,
            'non_budgeted_positions' => $nonBudgetedPositions,
            'occupied_positions' => $occupiedPositions,
            'vacant_positions' => $vacantPositions,
            'frozen_positions' => $frozenPositions,
            'eliminated_positions' => $eliminatedPositions,
            'total_budget_cost' => round($totalBudgetCost, 2),
        ];
    }
}
