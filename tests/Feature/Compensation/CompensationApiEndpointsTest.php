<?php

declare(strict_types=1);

namespace Tests\Feature\Compensation;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Models\CompensationRecommendation;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CompensationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompensationApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompensationPermissionSeeder::class);
    }

    public function test_compensation_cycle_crud_and_budget_api(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        // 1. POST /api/v1/hcm/compensation/cycles
        $response = $this->actingAs($user)->postJson('/api/v1/hcm/compensation/cycles', [
            'name' => '2026 Executive Review',
            'cycle_type' => 'annual_merit',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-06-30',
            'effective_on' => '2026-07-01',
            'currency' => 'USD',
        ]);

        $response->assertStatus(201);
        $cycleId = $response->json('data.id');

        // 2. PUT /api/v1/hcm/compensation/cycles/{cycle}/configuration
        $configResponse = $this->actingAs($user)->putJson("/api/v1/hcm/compensation/cycles/{$cycleId}/configuration", [
            'guidelines' => ['max_merit_pct' => 10.0],
        ]);
        $configResponse->assertStatus(200);
        $this->assertEquals('configured', $configResponse->json('data.status'));

        // 3. POST /api/v1/hcm/compensation/cycles/{cycle}/transition
        $transResponse = $this->actingAs($user)->postJson("/api/v1/hcm/compensation/cycles/{$cycleId}/transition", [
            'target_status' => 'open',
        ]);
        $transResponse->assertStatus(200);
        $this->assertEquals('open', $transResponse->json('data.status'));

        // 4. POST /api/v1/hcm/compensation/cycles/{cycle}/budgets
        $budgetResponse = $this->actingAs($user)->postJson("/api/v1/hcm/compensation/cycles/{$cycleId}/budgets", [
            'scope_type' => 'department',
            'scope_id' => 'dept-sales',
            'allocated' => 75000.0,
            'holdback_amount' => 5000.0,
        ]);
        $budgetResponse->assertStatus(201);
        $this->assertEquals(75000.0, (float) $budgetResponse->json('data.allocated'));

        // 5. GET /api/v1/hcm/compensation/cycles/{cycle}/budgets
        $getBudgets = $this->actingAs($user)->getJson("/api/v1/hcm/compensation/cycles/{$cycleId}/budgets");
        $getBudgets->assertStatus(200);
        $this->assertCount(1, $getBudgets->json('data'));
        $this->assertEquals(75000.0, $getBudgets->json('rollup.total_allocated'));
    }

    public function test_recommendation_propose_and_approve_api(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-API-001',
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'employment_status' => 'active',
            'joining_date' => '2020-01-01',
        ]);

        $cycle = CompensationCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Tech Merit',
            'cycle_type' => 'annual_merit',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'effective_on' => '2026-04-01',
            'status' => 'manager_planning',
        ]);

        // POST /api/v1/hcm/compensation/cycles/{cycle}/recommendations/{employee}/propose
        $propResponse = $this->actingAs($user)->postJson("/api/v1/hcm/compensation/cycles/{$cycle->id}/recommendations/{$employee->id}/propose", [
            'increase_percentage' => 7.5,
            'performance_rating' => 'exceeds',
            'justification' => 'Exceptional technical leadership.',
        ]);

        $propResponse->assertStatus(200);
        $recId = $propResponse->json('data.id');
        $this->assertEquals('proposed', $propResponse->json('data.status'));

        // POST /api/v1/hcm/compensation/recommendations/{recommendation}/approve
        $appResponse = $this->actingAs($user)->postJson("/api/v1/hcm/compensation/recommendations/{$recId}/approve");
        $appResponse->assertStatus(200);
        $this->assertEquals('approved', $appResponse->json('data.status'));
    }

    public function test_ai_draft_justification_endpoint(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $response = $this->actingAs($user)->postJson('/api/v1/hcm/compensation/ai/draft-justification', [
            'employee_name' => 'Frank Miller',
            'performance_rating' => 'outstanding',
            'compa_ratio' => 0.85,
            'proposed_percentage' => 8.0,
            'context' => 'Shipped major release ahead of schedule',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('data.is_advisory'));
        $this->assertStringContainsString('Frank Miller', $response->json('data.suggested_justification'));
    }
}
