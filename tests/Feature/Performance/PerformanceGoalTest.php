<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceGoal;
use App\Domains\Performance\Services\PerformanceGoalService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceGoalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_create_cascaded_goal_and_record_progress(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-PRF-01',
            'employee_number' => 'EMP-PRF-01',
            'first_name' => 'Tariq',
            'last_name' => 'Mahmood',
            'official_email' => 'tariq@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
        ]);

        $service = app(PerformanceGoalService::class);

        // 1. Create Parent / Strategic Goal
        $parentGoal = $service->create($user, [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'owner_type' => 'company',
            'title' => 'Increase Global Enterprise ARR by 20%',
            'goal_type' => 'strategic',
            'measurement_type' => 'target_value',
            'weight' => 0,
            'status' => 'active',
        ]);

        // 2. Create Employee Cascaded Goal
        $empGoal = $service->create($user, [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'parent_goal_id' => $parentGoal->id,
            'owner_type' => 'individual',
            'title' => 'Close 10 New Tier-1 Enterprise Accounts',
            'goal_type' => 'operational',
            'measurement_type' => 'target_value',
            'weight' => 50,
            'target_value' => 10,
            'current_value' => 0,
            'status' => 'active',
        ]);

        $this->assertEquals($parentGoal->id, $empGoal->parent_goal_id);
        $this->assertEquals(50.0, (float) $empGoal->weight);

        // 3. Record Progress
        $progress = $service->recordProgress($user, $empGoal, 6.0, 60.0, 'Closed 6 enterprise contracts ahead of Q3', 'manual');

        $this->assertEquals(60.0, (float) $progress->progress_percentage);
        $this->assertEquals(60.0, (float) $empGoal->fresh()->progress_percentage);
        $this->assertEquals(6.0, (float) $empGoal->fresh()->current_value);
    }
}
