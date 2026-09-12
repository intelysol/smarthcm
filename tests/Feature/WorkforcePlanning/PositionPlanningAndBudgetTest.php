<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Enums\PlanPositionStatus;
use App\Domains\WorkforcePlanning\Services\PositionPlanningService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionPlanningAndBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_creation_and_budget_calculation(): void
    {
        $tenant = Tenant::factory()->create();
        $planService = app(WorkforcePlanService::class);
        $positionService = app(PositionPlanningService::class);

        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-POS-01',
            'name' => 'Position Test Plan',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        $position = $positionService->createPosition($plan, [
            'position_code' => 'POS-DEV-001',
            'title' => 'Senior Backend Engineer',
            'status' => PlanPositionStatus::PLANNED->value,
            'is_budgeted' => true,
            'fte' => 1.00,
            'budget' => [
                'base_salary_budget' => 120000.00,
                'bonus_budget' => 15000.00,
                'benefits_budget' => 20000.00,
                'employer_contributions_budget' => 10000.00,
                'payroll_tax_budget' => 9500.00,
                'recruitment_cost_budget' => 5000.00,
                'equipment_cost_budget' => 3000.00,
            ],
        ]);

        $this->assertEquals('POS-DEV-001', $position->position_code);
        $this->assertNotNull($position->budget);

        // 120000 + 15000 + 20000 + 10000 + 9500 + 5000 + 3000 = 182500.00
        $this->assertEquals(182500.00, (float) $position->budget->total_employment_cost);

        // Freeze Position
        $frozen = $positionService->freezePosition($position);
        $this->assertEquals(PlanPositionStatus::FROZEN->value, $frozen->status);

        // Unfreeze Position
        $unfrozen = $positionService->unfreezePosition($frozen);
        $this->assertEquals(PlanPositionStatus::OPEN->value, $unfrozen->status);

        // Eliminate Position
        $eliminated = $positionService->eliminatePosition($unfrozen, 'Redundant role eliminated in org restructuring');
        $this->assertEquals(PlanPositionStatus::ELIMINATED->value, $eliminated->status);
        $this->assertEquals('Redundant role eliminated in org restructuring', $eliminated->elimination_reason);

        // Position Summary
        $summary = $positionService->getPositionSummary($tenant->id, $plan->id);
        $this->assertEquals(1, $summary['total_positions']);
        $this->assertEquals(1, $summary['eliminated_positions']);
    }
}
