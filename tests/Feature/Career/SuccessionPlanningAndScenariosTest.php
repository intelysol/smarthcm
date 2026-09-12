<?php

namespace Tests\Feature\Career;

use App\Domains\Career\Models\SuccessionCandidate;
use App\Domains\Career\Models\SuccessionPlan;
use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CareerPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuccessionPlanningAndScenariosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CareerPermissionSeeder::class);
    }

    public function test_succession_plan_critical_positions_and_scenario_simulation(): void
    {
        $tenant = Tenant::factory()->create();
        $execUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $incumbent = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-CTO-01',
            'employee_code' => 'EMP-CTO-01',
            'first_name' => 'Wasim',
            'last_name' => 'Akram',
            'joining_date' => now()->toDateString(),
        ]);

        $successor = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-PRIN-01',
            'employee_code' => 'EMP-PRIN-01',
            'first_name' => 'Shaheen',
            'last_name' => 'Afridi',
            'joining_date' => now()->toDateString(),
        ]);

        $job = Job::query()->create([
            'tenant_id' => $tenant->id,
            'job_code' => 'JOB-CTO',
            'title' => 'Chief Technology Officer',
        ]);

        $position = Position::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'job_id' => $job->id,
            'code' => 'POS-CTO-01',
            'position_code' => 'POS-CTO-01',
            'title' => 'Chief Technology Officer',
            'headcount' => 1,
        ]);

        $this->grant($execUser, [
            'hcm.succession.manage', 'hcm.succession.view', 'hcm.talent.confidential.view', 'hcm.talent.view'
        ]);

        // 1. Create Succession Plan
        $planResponse = $this->actingAs($execUser)->postJson('/api/v1/hcm/talent/succession/plans', [
            'name' => 'C-Suite Succession Master Plan 2026',
            'description' => 'Continuity and emergency readiness for executive roles',
        ]);
        $planResponse->assertStatus(201);
        $planId = $planResponse->json('data.id');

        // 2. Add Critical Position
        $posResponse = $this->actingAs($execUser)->postJson("/api/v1/hcm/talent/succession/plans/{$planId}/positions", [
            'position_id' => $position->id,
            'job_id' => $job->id,
            'current_incumbent_id' => $incumbent->id,
            'criticality' => 'business_critical',
            'vacancy_risk' => 'critical',
        ]);
        $posResponse->assertStatus(201);
        $succPosId = $posResponse->json('data.id');

        // 3. Add Successor Candidate
        $candResponse = $this->actingAs($execUser)->postJson("/api/v1/hcm/talent/succession/positions/{$succPosId}/candidates", [
            'employee_id' => $successor->id,
            'priority' => 1,
            'readiness_timeframe' => 'ready_under_1_year',
            'readiness_score' => 88.0,
            'potential_rating' => 4.8,
            'performance_rating' => 4.6,
            'is_emergency_choice' => true,
        ]);
        $candResponse->assertStatus(201);

        $this->assertDatabaseHas('succession_candidates', [
            'succession_position_id' => $succPosId,
            'employee_id' => $successor->id,
            'is_emergency_choice' => true,
        ]);

        // 4. Create Succession Scenario Simulation
        $scenarioResponse = $this->actingAs($execUser)->postJson("/api/v1/hcm/talent/succession/plans/{$planId}/scenarios", [
            'name' => 'Sudden Incumbent Departure Scenario',
            'trigger_event' => 'incumbent_leaves',
            'description' => 'Simulate emergency promotion of Lead Architect to Interim CTO',
        ]);
        $scenarioResponse->assertStatus(201);
        $scenarioId = $scenarioResponse->json('data.id');

        $scenCandResponse = $this->actingAs($execUser)->postJson("/api/v1/hcm/talent/succession/scenarios/{$scenarioId}/candidates", [
            'succession_position_id' => $succPosId,
            'proposed_successor_id' => $successor->id,
            'role_assignment' => 'interim',
            'impact_analysis' => 'Interim appointment will mitigate operational risk with zero downtime.',
        ]);
        $scenCandResponse->assertStatus(201);

        $this->assertDatabaseHas('succession_scenario_candidates', [
            'scenario_id' => $scenarioId,
            'proposed_successor_id' => $successor->id,
            'role_assignment' => 'interim',
        ]);
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'succession'], ['label' => 'Succession']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
