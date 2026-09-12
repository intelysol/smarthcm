<?php

namespace Tests\Feature\Learning;

use App\Domains\Learning\Models\LearningBudget;
use App\Domains\Learning\Models\LearningBudgetAllocation;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningTrainingCost;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningBudgetAndCostPrecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_training_budget_and_cost_exact_decimal_arithmetic(): void
    {
        $tenant = Tenant::factory()->create();

        $budget = LearningBudget::create([
            'tenant_id' => $tenant->id,
            'title' => 'FY2026 Enterprise Training Budget',
            'fiscal_year' => '2026',
            'total_allocated' => 500000.00,
            'total_spent' => 0.00,
            'currency' => 'USD',
            'status' => 'active',
        ]);

        $allocation = LearningBudgetAllocation::create([
            'tenant_id' => $tenant->id,
            'budget_id' => $budget->id,
            'allocated_to_type' => 'department',
            'allocated_to_id' => null,
            'amount' => 125000.00,
            'spent_amount' => 0.00,
        ]);

        $course = LearningCourse::create([
            'tenant_id' => $tenant->id,
            'title' => 'Executive Leadership Summit',
            'code' => 'EXEC-01',
            'delivery_type' => 'classroom',
            'status' => 'published',
            'duration_minutes' => 960,
        ]);

        // Record granular training costs (exact decimal precision, no floating-point distortion)
        LearningTrainingCost::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'cost_type' => 'instructor_fee',
            'amount' => 15000.50,
            'currency' => 'USD',
        ]);

        LearningTrainingCost::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'cost_type' => 'venue_hire',
            'amount' => 4500.75,
            'currency' => 'USD',
        ]);

        LearningTrainingCost::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'cost_type' => 'course_materials',
            'amount' => 1250.25,
            'currency' => 'USD',
        ]);

        // Total: 15000.50 + 4500.75 + 1250.25 = 20751.50
        $totalCost = LearningTrainingCost::where('tenant_id', $tenant->id)
            ->where('course_id', $course->id)
            ->sum('amount');

        $this->assertEquals(20751.50, (float) $totalCost);
        $this->assertEquals(500000.00, (float) $budget->total_allocated);
        $this->assertEquals(125000.00, (float) $allocation->amount);
    }
}
