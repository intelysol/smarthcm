<?php

namespace Tests\Feature\AiOperations;

use App\Domains\AiOperations\Contracts\AiOperationsInterface;
use App\Domains\AiOperations\Models\HcmAiEvalCase;
use App\Domains\AiOperations\Models\HcmAiEvalDataset;
use App\Domains\AiOperations\Models\HcmAiEvalRun;
use App\Domains\AiOperations\Models\HcmAiFeedback;
use App\Domains\AiOperations\Models\HcmAiImprovementItem;
use App\Domains\AiOperations\Models\HcmAiInteractionTelemetry;
use App\Domains\AiOperations\Models\HcmAiRegression;
use App\Domains\EmployeeAi\Contracts\EmployeeAiConciergeInterface;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AiOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected AiOperationsInterface $operationsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global Operations',
            'slug' => 'acme-ops',
            'tenant_code' => 'ACM-OPS-01',
            'status' => 'active',
        ]);

        $this->operationsService = app(AiOperationsInterface::class);
    }

    public function test_record_telemetry_and_feedback(): void
    {
        // 1. Ingest Interaction Telemetry
        $telemetry = $this->operationsService->recordTelemetry([
            'tenant_id' => $this->tenant->id,
            'use_case_code' => 'EMPLOYEE_CONCIERGE',
            'model_code' => 'GPT-4O-ENTERPRISE',
            'provider' => 'Azure OpenAI',
            'input_tokens' => 200,
            'output_tokens' => 150,
            'latency_ms' => 320,
            'tool_calls' => ['get_leave_balances'],
            'retrieval_citations' => ['Enterprise Leave Policy v4.2'],
            'grounding_score' => 98.0,
            'lifecycle_status' => 'DELIVERED',
        ]);

        $this->assertNotNull($telemetry->id);
        $this->assertEquals('EMPLOYEE_CONCIERGE', $telemetry->use_case_code);
        $this->assertEquals(200, $telemetry->input_tokens);
        $this->assertEquals(150, $telemetry->output_tokens);
        $this->assertGreaterThan(0, $telemetry->cost_estimate);

        // 2. Positive Feedback
        $feedbackPositive = $this->operationsService->recordFeedback([
            'tenant_id' => $this->tenant->id,
            'telemetry_id' => $telemetry->id,
            'is_positive' => true,
            'rating' => 5,
            'comment' => 'Fast and accurate answer',
        ]);

        $this->assertNotNull($feedbackPositive->id);
        $this->assertTrue($feedbackPositive->is_positive);

        // 3. Negative Feedback with Auto-Improvement Ingestion
        $feedbackNegative = $this->operationsService->recordFeedback([
            'tenant_id' => $this->tenant->id,
            'telemetry_id' => $telemetry->id,
            'is_positive' => false,
            'rating' => 1,
            'feedback_category' => 'INCORRECT',
            'comment' => 'Citations referenced outdated remote work policy section',
        ]);

        $this->assertNotNull($feedbackNegative->id);
        $this->assertFalse($feedbackNegative->is_positive);

        // Assert improvement backlog item created automatically
        $improvement = HcmAiImprovementItem::where('tenant_id', $this->tenant->id)->latest()->first();
        $this->assertNotNull($improvement);
        $this->assertEquals('IDENTIFIED', $improvement->status);
        $this->assertStringContainsString('INCORRECT', $improvement->evidence_summary);
    }

    public function test_golden_evaluation_run_and_regression_detection(): void
    {
        // 1. Create Golden Dataset and Cases
        $dataset = HcmAiEvalDataset::create([
            'tenant_id' => $this->tenant->id,
            'dataset_code' => 'DS-GOLDEN-HR-01',
            'name' => 'Golden HR Conversational Scenarios',
            'description' => 'Standard test cases for core HR policies and balances',
            'dataset_type' => 'GOLDEN_QUESTIONS',
            'total_cases' => 2,
        ]);

        HcmAiEvalCase::create([
            'tenant_id' => $this->tenant->id,
            'dataset_id' => $dataset->id,
            'case_code' => 'C-01',
            'use_case_code' => 'EMPLOYEE_CONCIERGE',
            'prompt_input' => 'What is the standard leave carryover limit?',
            'expected_behavior' => 'State 5 days carryover per Enterprise Leave Policy v4.2',
            'expected_sources' => ['Enterprise Leave Policy v4.2'],
            'expected_tools' => [],
        ]);

        // 2. Execute Baseline Evaluation Run
        $run1 = $this->operationsService->executeEvaluationRun(
            $this->tenant->id,
            $dataset->id,
            'GPT-4O-ENTERPRISE'
        );

        $this->assertNotNull($run1->id);
        $this->assertEquals('COMPLETED', $run1->status);
        $this->assertGreaterThan(90.0, $run1->overall_quality_score);

        // 3. Create a degraded run to test regression detection
        $run2 = HcmAiEvalRun::create([
            'tenant_id' => $this->tenant->id,
            'dataset_id' => $dataset->id,
            'run_code' => 'RUN-DEGRADED-TEST',
            'model_code' => 'TEST-CANDIDATE-MODEL',
            'provider' => 'OpenAI',
            'status' => 'COMPLETED',
            'accuracy_score' => 75.0, // Significant drop from baseline 95.0
            'grounding_score' => 80.0,
            'citation_score' => 80.0,
            'tool_correctness_score' => 80.0,
            'policy_compliance_score' => 100.0,
            'safety_score' => 100.0,
            'overall_quality_score' => 82.0,
            'avg_latency_ms' => 900, // Significant latency spike
        ]);

        $regressions = $this->operationsService->detectRegressions($this->tenant->id, $run2->id);
        $this->assertNotEmpty($regressions);

        $metrics = collect($regressions)->pluck('metric_name')->toArray();
        $this->assertContains('ACCURACY', $metrics);
        $this->assertContains('LATENCY', $metrics);
    }

    public function test_budget_policies_and_production_readiness(): void
    {
        // 1. Budget Policy
        $budget = $this->operationsService->checkBudgetStatus($this->tenant->id, 'TENANT', 'ALL');
        $this->assertEquals(500.00, $budget['budget_limit_usd']);
        $this->assertFalse($budget['threshold_exceeded']);

        // 2. Production Readiness Index
        $readiness = $this->operationsService->calculateProductionReadiness(
            $this->tenant->id,
            'EMPLOYEE_CONCIERGE'
        );

        $this->assertNotNull($readiness->id);
        $this->assertEquals('PRODUCTION_READY', $readiness->readiness_status);
        $this->assertGreaterThanOrEqual(90.0, $readiness->overall_readiness_score);
    }

    public function test_end_to_end_concierge_interaction_slice(): void
    {
        // Setup employee context
        $companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $companyId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Acme Operations HQ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employeeId = (string) Str::uuid();
        DB::table('employees')->insert([
            'id' => $employeeId,
            'tenant_id' => $this->tenant->id,
            'company_id' => $companyId,
            'employee_code' => 'EMP-OPS-202',
            'employee_number' => 'EMP-OPS-202',
            'first_name' => 'Alex',
            'last_name' => 'Vance',
            'employment_status' => 'ACTIVE',
            'joining_date' => '2024-01-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = (string) Str::uuid();
        $concierge = app(EmployeeAiConciergeInterface::class);

        // 1. Start Session
        $session = $concierge->startSession($this->tenant->id, $userId, $employeeId, 'EMPLOYEE');
        $this->assertNotNull($session->id);

        // 2. Chat Query: Annual leave days remaining
        $chatResult = $concierge->chat(
            $session->id,
            'How many annual leave days do I have remaining?',
            $this->tenant->id,
            $userId,
            $employeeId
        );

        $this->assertArrayHasKey('message', $chatResult);
        $this->assertStringContainsString('annual leave', $chatResult['message']['content']);

        // 3. Verify Telemetry was automatically recorded in HcmAiInteractionTelemetry
        $telemetry = HcmAiInteractionTelemetry::where('tenant_id', $this->tenant->id)
            ->where('session_id', $session->id)
            ->first();

        $this->assertNotNull($telemetry, 'Telemetry should be automatically recorded by concierge interaction');
        $this->assertEquals('EMPLOYEE_CONCIERGE', $telemetry->use_case_code);
        $this->assertEquals('DELIVERED', $telemetry->lifecycle_status);
        $this->assertGreaterThan(0, $telemetry->input_tokens);

        // 4. Submit Feedback on the message
        $feedback = $this->operationsService->recordFeedback([
            'tenant_id' => $this->tenant->id,
            'telemetry_id' => $telemetry->id,
            'message_id' => $chatResult['message']['id'],
            'user_id' => $userId,
            'rating' => 5,
            'is_positive' => true,
        ]);
        $this->assertNotNull($feedback->id);

        // 5. Verify Operations Dashboard aggregates the interaction
        $dashboard = $this->operationsService->getOperationsDashboard($this->tenant->id);
        $this->assertGreaterThanOrEqual(1, $dashboard['executive_health']['total_requests']);
        $this->assertEquals(100.0, $dashboard['executive_health']['success_rate']);
    }

    public function test_api_endpoints_and_web_dashboard(): void
    {
        // 1. Ingest telemetry via API
        $postTelemetryResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
        ])->postJson('/api/hcm/ai/operations/telemetry', [
            'use_case_code' => 'WORKFORCE_ANALYTICS',
            'model_code' => 'GPT-4O-ENTERPRISE',
            'provider' => 'Azure OpenAI',
            'input_tokens' => 180,
            'output_tokens' => 95,
            'latency_ms' => 290,
        ]);
        $postTelemetryResponse->assertStatus(201);

        // 2. Fetch Telemetry via API
        $getTelemetryResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
        ])->getJson('/api/hcm/ai/operations/telemetry');
        $getTelemetryResponse->assertStatus(200);
        $this->assertCount(1, $getTelemetryResponse->json('telemetry'));

        // 3. Post Feedback via API
        $feedbackResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
        ])->postJson('/api/hcm/ai/feedback', [
            'is_positive' => true,
            'rating' => 5,
            'comment' => 'Excellent query response',
        ]);
        $feedbackResponse->assertStatus(201);

        // 4. Fetch Operations Dashboard via API
        $dashboardResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
        ])->getJson('/api/hcm/ai/operations/dashboard');

        $dashboardResponse->assertStatus(200)
            ->assertJsonStructure([
                'executive_health',
                'quality_metrics',
                'economics',
                'performance',
                'production_readiness',
            ]);

        // 5. Fetch Readiness via API
        $readinessResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
        ])->getJson('/api/hcm/ai/operations/readiness?use_case=EMPLOYEE_CONCIERGE');

        $readinessResponse->assertStatus(200)
            ->assertJsonPath('production_readiness.readiness_status', 'PRODUCTION_READY');

        // 6. Fetch Model Benchmarks via API
        $benchmarksResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
        ])->getJson('/api/hcm/ai/operations/model-benchmarks');

        $benchmarksResponse->assertStatus(200);
        $this->assertNotEmpty($benchmarksResponse->json('model_benchmarks'));

        // 7. Render Web Dashboard
        $webResponse = $this->get('/ai-operations/dashboard?tenant_id=' . $this->tenant->id);
        $webResponse->assertStatus(200);
        $webResponse->assertSee('AI Operations & Production Intelligence', false);
        $webResponse->assertSee('Quality Evaluation, Telemetry, Economics & Continuous Improvement', false);
        $webResponse->assertSee('PRODUCTION_READY');
    }
}
