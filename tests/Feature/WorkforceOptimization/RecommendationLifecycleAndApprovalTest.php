<?php

namespace Tests\Feature\WorkforceOptimization;

use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOpportunity;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Domains\WorkforceOptimization\Services\WorkforceActionService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationLifecycleAndApprovalTest extends TestCase
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

    public function test_recommendation_lifecycle_with_approval_and_downstream_action_creation(): void
    {
        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'Support BU', 'code' => 'SUP']);
        $dept = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Tier 1 Support', 'department_code' => 'SUP-T1', 'business_unit_id' => $bu->id]);

        $optimizationService = app(WorkforceOptimizationInterface::class);
        $run = $optimizationService->runOptimization($this->tenant->id, scope: ['department_id' => $dept->id]);

        $rec = $run->recommendations->first();
        $this->assertNotNull($rec);
        $this->assertEquals('GENERATED', $rec->lifecycle_status);
        $this->assertGreaterThan(0, $rec->decision_score);

        // Verify recommendation factors are populated
        $this->assertNotEmpty($rec->factors);

        // Approve recommendation
        $actionService = app(WorkforceActionService::class);
        $action = $actionService->approveRecommendation($rec, $this->user, 'Approved for immediate execution');

        $this->assertEquals('APPROVED', $rec->fresh()->lifecycle_status);
        $this->assertEquals($this->user->id, $rec->fresh()->reviewed_by);
        $this->assertNotNull($action);
        $this->assertEquals('pending', $action->status);
        $this->assertEquals($rec->id, $action->recommendation_id);

        // Verify audit log entry
        $this->assertDatabaseHas('hcm_workforce_optimization_audits', [
            'target_id' => $rec->id,
            'action' => 'APPROVED',
            'user_id' => $this->user->id,
        ]);

        // Execute action
        $actionService->executeAction($action);
        $this->assertEquals('dispatched', $action->fresh()->status);
        $this->assertEquals('EXECUTING', $rec->fresh()->lifecycle_status);
    }

    public function test_recommendation_rejection_with_reason_and_audit(): void
    {
        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'Sales BU', 'code' => 'SLS']);
        $dept = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Outbound Sales', 'department_code' => 'SLS-OUT', 'business_unit_id' => $bu->id]);

        $optimizationService = app(WorkforceOptimizationInterface::class);
        $run = $optimizationService->runOptimization($this->tenant->id, scope: ['department_id' => $dept->id]);

        $rec = $run->recommendations->first();
        $this->assertNotNull($rec);

        $actionService = app(WorkforceActionService::class);
        $actionService->rejectRecommendation(
            $rec,
            $this->user,
            'BUDGET_RESTRICTION',
            'Q4 hiring freeze currently in effect.'
        );

        $this->assertEquals('REJECTED', $rec->fresh()->lifecycle_status);
        $this->assertStringContainsString('BUDGET_RESTRICTION', $rec->fresh()->review_notes);

        $this->assertDatabaseHas('hcm_workforce_optimization_audits', [
            'target_id' => $rec->id,
            'action' => 'REJECTED',
            'user_id' => $this->user->id,
        ]);
    }
}
