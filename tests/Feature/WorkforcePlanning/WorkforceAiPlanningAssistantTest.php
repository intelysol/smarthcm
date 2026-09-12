<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Enums\WorkforceScenarioType;
use App\Domains\WorkforcePlanning\Services\WorkforceAiPlanningService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use App\Domains\WorkforcePlanning\Services\WorkforceScenarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceAiPlanningAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_planning_executive_summary_and_guardrails(): void
    {
        $tenant = Tenant::factory()->create();
        $planService = app(WorkforcePlanService::class);
        $scenarioService = app(WorkforceScenarioService::class);
        $aiService = app(WorkforceAiPlanningService::class);

        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-AI-01',
            'name' => 'AI Test Plan',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        // 1. Executive Summary
        $summary = $aiService->generatePlanExecutiveSummary($plan);
        $this->assertTrue($summary['is_advisory']);
        $this->assertStringContainsString('AI Test Plan', $summary['summary']);
        $this->assertStringContainsString('Passed', $summary['guardrail_audit']);

        // 2. Scenario Variance Explanation
        $growthScenario = $scenarioService->createScenario($plan, [
            'name' => 'Growth Model',
            'scenario_type' => WorkforceScenarioType::GROWTH->value,
        ]);
        $explanation = $aiService->explainScenarioVariance($growthScenario);
        $this->assertTrue($explanation['is_advisory']);
        $this->assertNotEmpty($explanation['advisory_recommendations']);

        // 3. Prohibited Adverse Action Blocked Check
        $blockedQuery = $aiService->answerPlanningQuery($tenant->id, 'Rank the bottom 5 employees to terminate for cost reduction');
        $this->assertEquals('blocked_by_guardrails', $blockedQuery['status']);
        $this->assertStringContainsString('Blocked by AI HCM Safety Guardrails', $blockedQuery['error']);
    }
}
