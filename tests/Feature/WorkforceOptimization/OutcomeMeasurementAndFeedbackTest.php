<?php

namespace Tests\Feature\WorkforceOptimization;

use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Domains\WorkforceOptimization\Services\OptimizationFeedbackService;
use App\Domains\WorkforceOptimization\Services\OptimizationOutcomeService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutcomeMeasurementAndFeedbackTest extends TestCase
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

    public function test_can_record_realized_outcome_and_variance(): void
    {
        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'Finance BU', 'code' => 'FIN']);
        $dept = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Accounting', 'department_code' => 'ACC-01', 'business_unit_id' => $bu->id]);

        $optimizationService = app(WorkforceOptimizationInterface::class);
        $run = $optimizationService->runOptimization($this->tenant->id, scope: ['department_id' => $dept->id]);
        $rec = $run->recommendations->first();

        $outcomeService = app(OptimizationOutcomeService::class);
        $outcome = $outcomeService->measureOutcome(
            recommendation: $rec,
            metricCategory: 'COST',
            metricName: 'LaborCostTotal',
            baselineValue: 50000.0,
            actualValue: 48000.0,
            windowDays: 30
        );

        $this->assertDatabaseHas('hcm_workforce_optimization_outcomes', [
            'id' => $outcome->id,
            'recommendation_id' => $rec->id,
            'metric_name' => 'LaborCostTotal',
        ]);
        $this->assertEquals(50000.0, $outcome->pre_action_value);
        $this->assertEquals(48000.0, $outcome->post_action_value);
    }

    public function test_can_record_manager_feedback_and_practicality_scores(): void
    {
        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'IT BU', 'code' => 'IT']);
        $dept = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Infra', 'department_code' => 'IT-INFRA', 'business_unit_id' => $bu->id]);

        $optimizationService = app(WorkforceOptimizationInterface::class);
        $run = $optimizationService->runOptimization($this->tenant->id, scope: ['department_id' => $dept->id]);
        $rec = $run->recommendations->first();

        $feedbackService = app(OptimizationFeedbackService::class);
        $feedback = $feedbackService->recordFeedback(
            recommendation: $rec,
            user: $this->user,
            feedbackType: 'MODIFIED',
            reasonCode: 'TIMELINE_ADJUSTMENT',
            narrative: 'Postponed execution by 2 weeks due to project release freeze.',
            feasibilityScore: 85,
            practicalityScore: 90
        );

        $this->assertDatabaseHas('hcm_workforce_optimization_feedback', [
            'id' => $feedback->id,
            'recommendation_id' => $rec->id,
            'user_id' => $this->user->id,
            'feedback_type' => 'MODIFIED',
        ]);
    }
}
