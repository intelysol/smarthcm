<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\Competency;
use App\Domains\Performance\Models\CompetencyCategory;
use App\Domains\Performance\Models\CompetencyFramework;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceImprovementPlan;
use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_pip_and_outcomes_api_endpoints(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-API-01',
            'employee_number' => 'EMP-API-01',
            'first_name' => 'API',
            'last_name' => 'Tester',
            'official_email' => 'api.tester@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => 'API Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
        ]);

        // 1. Create PIP via API
        $pipResponse = $this->actingAs($user)->postJson('/api/v1/hcm/performance/pips', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'cycle_id' => $cycle->id,
            'summary' => 'Underperformance on sprint deliverable milestones.',
        ]);
        $pipResponse->assertStatus(201);
        $pipId = $pipResponse->json('data.id');

        // 2. Add PIP Action via API
        $actionResponse = $this->actingAs($user)->postJson("/api/v1/hcm/performance/pips/{$pipId}/actions", [
            'title' => 'Complete peer-review remediation training',
            'due_date' => now()->addDays(30)->toDateString(),
        ]);
        $actionResponse->assertStatus(201);

        // 3. Conclude PIP via API
        $concludeResponse = $this->actingAs($user)->postJson("/api/v1/hcm/performance/pips/{$pipId}/conclude", [
            'status' => 'successful',
            'conclusion_notes' => 'Successfully met all remediated targets.',
        ]);
        $concludeResponse->assertStatus(200);
        $this->assertEquals('successful', $concludeResponse->json('data.status'));

        // 4. Publish Final Outcome via API
        $outcomeResponse = $this->actingAs($user)->postJson('/api/v1/hcm/performance/outcomes/publish', [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'final_rating' => 4.2,
            'summary' => 'Strong recovery and solid operational outcomes.',
        ]);
        $outcomeResponse->assertStatus(201);
        $outcomeId = $outcomeResponse->json('data.id');

        // 5. Acknowledge Outcome via API
        $ackResponse = $this->actingAs($user)->postJson("/api/v1/hcm/performance/outcomes/{$outcomeId}/acknowledge", [
            'comment' => 'Acknowledged review findings and goals for next cycle.',
        ]);
        $ackResponse->assertStatus(200);
        $this->assertEquals('acknowledged', $ackResponse->json('data.acknowledgement_status'));

        // 6. Cycle Metrics API
        $metricsResponse = $this->actingAs($user)->getJson("/api/v1/hcm/performance/cycles/{$cycle->id}/metrics");
        $metricsResponse->assertStatus(200);
        $this->assertArrayHasKey('cycle_id', $metricsResponse->json('data'));
    }
}