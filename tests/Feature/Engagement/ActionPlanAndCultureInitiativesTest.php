<?php

namespace Tests\Feature\Engagement;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\CultureInitiative;
use App\Domains\Engagement\Models\EngagementActionPlan;
use App\Domains\Engagement\Services\CultureInitiativeService;
use App\Domains\Engagement\Services\EngagementActionPlanService;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EngagementPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActionPlanAndCultureInitiativesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EngagementPermissionSeeder::class);
    }

    public function test_action_plan_and_culture_initiatives_lifecycle(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $emp = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-PLAN-01',
            'employee_code' => 'EMP-PLAN-01',
            'first_name' => 'Ayesha',
            'last_name' => 'Malik',
            'joining_date' => now()->toDateString(),
        ]);

        $planService = app(EngagementActionPlanService::class);
        $cultureService = app(CultureInitiativeService::class);

        // 1. Create Action Plan with Tasks
        $plan = $planService->createActionPlan($tenant->id, [
            'title' => 'Leadership Transparency Improvement',
            'description' => 'Address low leadership communication scores from Q2 pulse.',
            'scope_type' => 'company',
            'owner_id' => $emp->id,
            'due_date' => now()->addMonths(3)->toDateString(),
            'priority' => 'high',
            'items' => [
                ['title' => 'Host Monthly Townhall Ask-Me-Anything Sessions', 'due_date' => now()->addMonth()->toDateString()],
                ['title' => 'Launch Executive Open Door Office Hours', 'due_date' => now()->addMonths(2)->toDateString()],
            ],
        ], $user->id);

        $this->assertEquals('open', $plan->status);
        $this->assertEquals(2, $plan->items()->count());

        // Update task completion
        $item = $plan->items()->first();
        $updatedItem = $planService->updateActionItem($item, [
            'status' => 'completed',
            'completion_percentage' => 100,
        ]);
        $this->assertEquals('completed', $updatedItem->status);

        // 2. Culture Initiative
        $initiative = $cultureService->createInitiative($tenant->id, [
            'code' => 'CULT-EMPATHY-2026',
            'title' => 'Inclusive Workplace Initiative',
            'category' => 'inclusion',
            'owner_id' => $emp->id,
            'start_date' => now()->toDateString(),
            'employees_reached' => 250,
            'actions' => [
                ['title' => 'Unconscious Bias Training Workshop'],
            ],
        ], $user->id);

        $this->assertEquals('active', $initiative->status);
        $this->assertEquals(1, $initiative->actions()->count());

        $completedInit = $cultureService->completeInitiative($initiative, $user->id);
        $this->assertEquals('completed', $completedInit->status);

        // 3. Engagement Goal Tracking
        $goal = $cultureService->createGoal($tenant->id, [
            'title' => 'Achieve +40 eNPS by Q4',
            'metric_type' => 'enps',
            'baseline_value' => 15.0,
            'target_value' => 40.0,
            'deadline' => now()->addMonths(6)->toDateString(),
            'owner_id' => $emp->id,
        ]);

        $this->assertEquals('active', $goal->status);

        // Update progress reaching target -> status achieved
        $achievedGoal = $cultureService->updateGoalProgress($goal, 42.0);
        $this->assertEquals('achieved', $achievedGoal->status);
    }
}
