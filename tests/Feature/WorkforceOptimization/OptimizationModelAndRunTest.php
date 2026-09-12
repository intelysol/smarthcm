<?php

namespace Tests\Feature\WorkforceOptimization;

use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationModel;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationModelVersion;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRun;
use App\Domains\WorkforceOptimization\Services\OptimizationModelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptimizationModelAndRunTest extends TestCase
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

    public function test_can_create_optimization_model_with_objectives_and_constraints(): void
    {
        $service = app(OptimizationModelService::class);

        $model = $service->createModel([
            'tenant_id' => $this->tenant->id,
            'name' => 'Operational Efficiency Model',
            'model_type' => 'balanced',
        ]);

        $this->assertDatabaseHas('hcm_workforce_optimization_models', [
            'id' => $model->id,
            'name' => 'Operational Efficiency Model',
        ]);

        $version = $service->createModelVersion(
            $model,
            ['change_notes' => 'v1.0 Standard Weighted', 'is_current' => true],
            [
                ['code' => 'MIN_COST', 'name' => 'Minimize Labor Cost', 'direction' => 'MINIMIZE', 'weight' => 1.5, 'target_metric' => 'labor_cost'],
                ['code' => 'MAX_CAPACITY', 'name' => 'Maximize Capacity Fulfillment', 'direction' => 'MAXIMIZE', 'weight' => 1.0, 'target_metric' => 'capacity_hours'],
            ],
            [
                ['constraint_type' => 'working_hours', 'name' => 'Department Budget Cap', 'is_hard_constraint' => true],
            ]
        );

        $this->assertDatabaseHas('hcm_workforce_optimization_model_versions', [
            'id' => $version->id,
            'version' => 1,
            'is_current' => true,
        ]);

        $this->assertDatabaseHas('hcm_workforce_optimization_objectives', [
            'model_id' => $model->id,
            'code' => 'MIN_COST',
        ]);

        $this->assertDatabaseHas('hcm_workforce_optimization_constraints', [
            'model_id' => $model->id,
            'name' => 'Department Budget Cap',
        ]);
    }

    public function test_can_execute_optimization_run_via_service_and_api(): void
    {
        $optimizationService = app(WorkforceOptimizationInterface::class);

        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $this->tenant->id]);

        $bu = BusinessUnit::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $company->id,
            'name' => 'Engineering Unit',
            'code' => 'ENG',
        ]);

        $dept = Department::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $company->id,
            'department_name' => 'Cloud Platform',
            'department_code' => 'ENG-CLOUD',
            'business_unit_id' => $bu->id,
        ]);

        $run = $optimizationService->runOptimization(
            tenantId: $this->tenant->id,
            scope: ['department_id' => $dept->id],
            triggeredBy: $this->user
        );

        $this->assertNotNull($run);
        $this->assertEquals('COMPLETED', $run->status);
        $this->assertDatabaseHas('hcm_workforce_optimization_runs', [
            'id' => $run->id,
            'status' => 'COMPLETED',
        ]);

        // API Endpoint test
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/workforce-optimization/runs/{$run->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $run->id);
    }
}
