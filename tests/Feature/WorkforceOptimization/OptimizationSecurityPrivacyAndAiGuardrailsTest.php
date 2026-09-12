<?php

namespace Tests\Feature\WorkforceOptimization;

use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use App\Domains\WorkforceOptimization\Services\AdvisoryWorkforceOptimizationAiService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptimizationSecurityPrivacyAndAiGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_ai_explanations_are_strictly_advisory_with_guardrail_disclaimers(): void
    {
        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'Legal BU', 'code' => 'LGL']);
        $dept = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Compliance', 'department_code' => 'LGL-CMP', 'business_unit_id' => $bu->id]);

        $optimizationService = app(WorkforceOptimizationInterface::class);
        $run = $optimizationService->runOptimization($this->tenant->id, scope: ['department_id' => $dept->id]);
        $rec = $run->recommendations->first();

        $aiService = app(AdvisoryWorkforceOptimizationAiService::class);
        $explanation = $aiService->generateAdvisoryExplanation($rec);

        $this->assertArrayHasKey('executive_summary', $explanation);
        $this->assertArrayHasKey('strategic_trade_off_breakdown', $explanation);
        $this->assertArrayHasKey('governance_note', $explanation);

        // Verify guardrail notice
        $this->assertStringContainsString('advisory only', strtolower($explanation['governance_note']));
        $this->assertStringContainsString('authorization is mandatory', strtolower($explanation['governance_note']));

        // Verify API endpoint delivers advisory response
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/workforce-optimization/ai/explain/{$rec->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('recommendation_id', $rec->id)
            ->assertJsonStructure([
                'success',
                'recommendation_id',
                'data' => [
                    'executive_summary',
                    'strategic_trade_off_breakdown',
                    'governance_note',
                ]
            ]);
    }

    public function test_multi_tenant_isolation_in_optimization_runs(): void
    {
        $otherTenant = Tenant::factory()->create();
        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'Ops BU', 'code' => 'OPS']);
        $dept = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Security', 'department_code' => 'OPS-SEC', 'business_unit_id' => $bu->id]);

        $optimizationService = app(WorkforceOptimizationInterface::class);
        $run = $optimizationService->runOptimization($this->tenant->id, scope: ['department_id' => $dept->id]);

        // Querying for other tenant should return zero runs
        $otherRunQuery = \App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRun::where('tenant_id', $otherTenant->id)->get();
        $this->assertCount(0, $otherRunQuery);
    }
}
