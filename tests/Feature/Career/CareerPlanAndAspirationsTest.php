<?php

namespace Tests\Feature\Career;

use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\CareerPlanAction;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Job;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CareerPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerPlanAndAspirationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CareerPermissionSeeder::class);
    }

    public function test_employee_can_set_aspirations_create_plan_and_manager_can_approve(): void
    {
        $tenant = Tenant::factory()->create();
        $mgrUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $empUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $manager = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $mgrUser->id,
            'company_id' => $company->id,
            'employee_number' => 'MGR-PLN-01',
            'employee_code' => 'MGR-PLN-01',
            'first_name' => 'Rashid',
            'last_name' => 'Latif',
            'joining_date' => now()->toDateString(),
        ]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $empUser->id,
            'company_id' => $company->id,
            'reporting_manager_id' => $manager->id,
            'employee_number' => 'EMP-PLN-01',
            'employee_code' => 'EMP-PLN-01',
            'first_name' => 'Shoaib',
            'last_name' => 'Akhtar',
            'joining_date' => now()->toDateString(),
        ]);

        $targetJob = Job::query()->create([
            'tenant_id' => $tenant->id,
            'job_code' => 'JOB-DIR-ENG',
            'title' => 'Director of Engineering',
        ]);

        // 1. Employee sets career aspiration
        $aspirationResponse = $this->actingAs($empUser)->postJson('/api/v1/hcm/me/career/aspirations', [
            'target_job_id' => $targetJob->id,
            'target_career_level' => 'Executive',
            'preferred_timeline_months' => 24,
            'preferred_location' => 'Islamabad & Remote',
            'interest_areas' => ['Engineering Management', 'System Architecture'],
            'preferences' => ['leadership', 'remote'],
            'visibility' => 'manager_shared',
        ]);
        $aspirationResponse->assertStatus(200);

        $this->assertDatabaseHas('employee_career_aspirations', [
            'employee_id' => $employee->id,
            'target_job_id' => $targetJob->id,
            'visibility' => 'manager_shared',
        ]);

        // 2. Employee creates Individual Development Plan (IDP)
        $planResponse = $this->actingAs($empUser)->postJson('/api/v1/hcm/me/career/plans', [
            'target_job_id' => $targetJob->id,
            'target_date' => now()->addMonths(18)->toDateString(),
            'visibility' => 'manager',
        ]);
        $planResponse->assertStatus(201);
        $planId = $planResponse->json('data.id');

        // 3. Employee adds development action
        $actionResponse = $this->actingAs($empUser)->postJson("/api/v1/hcm/me/career/plans/{$planId}/actions", [
            'action_type' => 'project',
            'title' => 'Lead Global Microservices Modernization Project',
            'description' => 'Drive cross-functional architectural overhaul.',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addMonths(6)->toDateString(),
        ]);
        $actionResponse->assertStatus(201);
        $actionId = $actionResponse->json('data.id');

        // 4. Update action progress
        $progressResponse = $this->actingAs($empUser)->patchJson("/api/v1/hcm/me/career/actions/{$actionId}/progress", [
            'completion_percentage' => 100.0,
            'status' => 'completed',
        ]);
        $progressResponse->assertStatus(200);

        $this->assertDatabaseHas('career_plan_actions', [
            'id' => $actionId,
            'completion_percentage' => 100.0,
            'status' => 'completed',
        ]);

        // 5. Manager approves Career Plan
        $approveResponse = $this->actingAs($mgrUser)->postJson("/api/v1/hcm/manager/career/plans/{$planId}/approve");
        $approveResponse->assertStatus(200);

        $this->assertDatabaseHas('career_plans', [
            'id' => $planId,
            'status' => 'approved',
            'approved_by' => $manager->id,
        ]);
    }
}
