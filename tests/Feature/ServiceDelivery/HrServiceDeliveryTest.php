<?php

namespace Tests\Feature\ServiceDelivery;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\SelfService\Enums\ServiceRequestPriority;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrKnowledgeCategory;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceRequestComment;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Domains\ServiceDelivery\Services\HrCaseOrchestrationService;
use App\Domains\ServiceDelivery\Services\HrServiceDeflectionService;
use App\Domains\ServiceDelivery\Services\HrServiceDeliveryCommandCenterService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrServiceDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected Department $department;
    protected Employee $employee;
    protected User $employeeUser;
    protected User $hrUser;
    protected HrServiceCategory $serviceCategory;
    protected HrServiceDefinition $serviceDefinition;
    protected HrServiceQueue $queue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->company = Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'HQ Unit',
            'code' => 'BU-HQ',
        ]);
        $this->department = Department::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'business_unit_id' => $bu->id,
            'department_name' => 'HR Operations',
            'department_code' => 'HRO-01',
        ]);

        $this->employeeUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'user_id' => $this->employeeUser->id,
            'employee_code' => 'EMP-SD-001',
            'employee_number' => 'EMP-SD-001',
            'first_name' => 'Jordan',
            'last_name' => 'Lee',
            'official_email' => 'jordan.lee@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $this->hrUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->serviceCategory = HrServiceCategory::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'CAT-OPS',
            'name' => 'Operations & Benefits',
        ]);

        $this->serviceDefinition = HrServiceDefinition::create([
            'tenant_id' => $this->tenant->id,
            'hr_service_category_id' => $this->serviceCategory->id,
            'service_code' => 'SVC-BENEFIT',
            'name' => 'Health Benefits Enrollment',
            'sla_hours' => 24,
            'is_active' => true,
        ]);

        $this->queue = HrServiceQueue::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'Q-BENEFITS',
            'name' => 'Benefits Tier 1 Queue',
            'max_workload' => 20,
            'is_active' => true,
        ]);
    }

    public function test_command_center_cockpit_metrics_and_sla_aggregation(): void
    {
        Carbon::setTestNow('2026-09-12 10:00:00');

        // Create 2 open requests: 1 on-time, 1 overdue
        $case1 = HrServiceRequest::create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'hr_service_definition_id' => $this->serviceDefinition->id,
            'request_number' => 'REQ-2026-0001',
            'subject' => 'Dental Coverage Query',
            'priority' => ServiceRequestPriority::NORMAL->value,
            'status' => ServiceRequestStatus::SUBMITTED->value,
            'due_at' => Carbon::now()->addHours(12),
            'assigned_queue_id' => $this->queue->id,
        ]);

        $case2 = HrServiceRequest::create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'hr_service_definition_id' => $this->serviceDefinition->id,
            'request_number' => 'REQ-2026-0002',
            'subject' => 'Vision Claim Escalation',
            'priority' => ServiceRequestPriority::URGENT->value,
            'status' => ServiceRequestStatus::IN_PROGRESS->value,
            'due_at' => Carbon::now()->subHours(2), // Overdue
            'assigned_queue_id' => $this->queue->id,
        ]);

        $commandCenterService = app(HrServiceDeliveryCommandCenterService::class);
        $metrics = $commandCenterService->getCommandCenterMetrics($this->tenant->id);

        $this->assertEquals(2, $metrics['summary']['open_cases']);
        $this->assertEquals(2, $metrics['summary']['new_today']);
        $this->assertEquals(1, $metrics['summary']['overdue']);
        $this->assertEquals(94.0, $metrics['summary']['sla_compliance_rate']);
        $this->assertCount(1, $metrics['sla_risk_cases']);
        $this->assertEquals('REQ-2026-0002', $metrics['sla_risk_cases'][0]['case_number']);
        $this->assertNotEmpty($metrics['queue_health']);
    }

    public function test_unified_case_inbox_filtering_and_timeline_orchestration(): void
    {
        // 1. Create a request
        $case = HrServiceRequest::create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'hr_service_definition_id' => $this->serviceDefinition->id,
            'request_number' => 'REQ-2026-0003',
            'subject' => 'Maternity Leave Policy Support',
            'description' => 'Need clarification on paid leave duration.',
            'priority' => ServiceRequestPriority::HIGH->value,
            'status' => ServiceRequestStatus::SUBMITTED->value,
            'assigned_queue_id' => $this->queue->id,
        ]);

        $orchestrationService = app(HrCaseOrchestrationService::class);

        // 2. Filter cases
        $inbox = $orchestrationService->getFilteredCases($this->tenant->id, [
            'status' => 'submitted',
            'priority' => 'high',
        ]);
        $this->assertEquals(1, $inbox->total());
        $this->assertEquals('REQ-2026-0003', $inbox->first()->request_number);

        // 3. Add Public Comment
        $pubComment = $orchestrationService->addComment(
            $case,
            'Here is the standard policy summary document.',
            'public',
            $this->hrUser
        );
        $this->assertEquals('public', $pubComment->comment_type);

        // 4. Add Confidential Internal HR Note
        $intNote = $orchestrationService->addComment(
            $case,
            'Confirmed with Legal that employee qualifies for full 16 weeks.',
            'internal',
            $this->hrUser
        );
        $this->assertEquals('internal', $intNote->comment_type);

        // 5. Verify Timeline contains public comment and internal note
        $timeline = $orchestrationService->getCaseTimeline($case, true);
        $this->assertGreaterThanOrEqual(2, count($timeline));

        // 6. Grounded AI Summary Verification (Non-autonomous advisory)
        $aiSummary = $orchestrationService->generateAiCaseSummary($case);
        $this->assertTrue($aiSummary['is_advisory_only']);
        $this->assertArrayHasKey('summary', $aiSummary);
        $this->assertArrayHasKey('citations', $aiSummary);
        $this->assertStringContainsString('Need clarification on paid leave duration', $aiSummary['summary']['issue']);

        // 7. Resolve Case
        $resolvedCase = $orchestrationService->resolveCase($case, $this->hrUser, 'Provided policy guide and confirmed eligibility.');
        $this->assertEquals(ServiceRequestStatus::RESOLVED->value, $resolvedCase->status);
        $this->assertNotNull($resolvedCase->resolved_at);
    }

    public function test_api_endpoints_and_horizontal_security(): void
    {
        // 1. Authenticated HR User gets dashboard API
        $response = $this->actingAs($this->hrUser)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->getJson('/api/hr-services/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'summary' => ['open_cases', 'new_today', 'overdue', 'sla_compliance_rate', 'csat_average'],
                'queue_health',
                'sla_risk_cases',
                'deflection',
            ]);

        // 2. Query Cases API
        $response = $this->actingAs($this->hrUser)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->getJson('/api/hr-services/cases');

        $response->assertStatus(200);

        // 3. Sensitive case isolation: Employee cannot access another employee's case
        $otherEmployee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'user_id' => User::factory()->create(['tenant_id' => $this->tenant->id])->id,
            'employee_code' => 'EMP-SD-999',
            'employee_number' => 'EMP-SD-999',
            'first_name' => 'Secret',
            'last_name' => 'Person',
            'official_email' => 'secret@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $confidentialCase = HrServiceRequest::create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $otherEmployee->id,
            'hr_service_definition_id' => $this->serviceDefinition->id,
            'request_number' => 'REQ-CONF-0001',
            'subject' => 'Confidential Inquiry',
            'confidentiality_level' => 'restricted',
            'priority' => ServiceRequestPriority::NORMAL->value,
            'status' => ServiceRequestStatus::SUBMITTED->value,
        ]);

        // When $this->employeeUser tries to view $confidentialCase -> 403 Forbidden
        $response = $this->actingAs($this->employeeUser)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->getJson("/api/hr-services/cases/{$confidentialCase->id}");

        $response->assertStatus(403);
    }

    public function test_web_portal_blade_views_render_successfully(): void
    {
        // 1. Command Center Portal view
        $response = $this->actingAs($this->hrUser)
            ->get('/portal/hr-services');
        $response->assertStatus(200);
        $response->assertSee('HR Service Delivery Command Center');

        // 2. Case Inbox Portal view
        $response = $this->actingAs($this->hrUser)
            ->get('/portal/hr-services/cases');
        $response->assertStatus(200);
        $response->assertSee('Unified Case Inbox & Workbench', false);

        // 3. Service Catalog Portal view
        $response = $this->actingAs($this->hrUser)
            ->get('/portal/hr-services/catalog');
        $response->assertStatus(200);
        $response->assertSee('HR Service Catalog & Intake', false);

        // 4. Knowledge Base Portal view
        $response = $this->actingAs($this->hrUser)
            ->get('/portal/hr-services/knowledge');
        $response->assertStatus(200);
        $response->assertSee('HR Knowledge Base & Self-Service Deflection', false);
    }
}
