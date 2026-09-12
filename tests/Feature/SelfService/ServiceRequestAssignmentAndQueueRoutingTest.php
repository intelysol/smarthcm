<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Enums\AssignmentMethod;
use App\Domains\SelfService\Models\HrServiceAssignmentRule;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceQueueMember;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\RequestAssignmentService;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestAssignmentAndQueueRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_rules_and_queue_round_robin(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        
        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-200',
            'employee_number' => 'EMP-200',
            'first_name' => 'Bob',
            'last_name' => 'Marley',
            'official_email' => 'bob@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $agent1 = User::factory()->create(['tenant_id' => $tenant->id]);
        $agent2 = User::factory()->create(['tenant_id' => $tenant->id]);

        $queue = HrServiceQueue::create([
            'tenant_id' => $tenant->id,
            'code' => 'PAYROLL-OPS',
            'name' => 'Payroll Operations Queue',
            'assignment_method' => AssignmentMethod::WORKLOAD_BASED->value,
            'is_active' => true,
        ]);

        $member1 = HrServiceQueueMember::create([
            'tenant_id' => $tenant->id,
            'hr_service_queue_id' => $queue->id,
            'user_id' => $agent1->id,
            'active_tickets_count' => 5,
            'is_available' => true,
        ]);

        $member2 = HrServiceQueueMember::create([
            'tenant_id' => $tenant->id,
            'hr_service_queue_id' => $queue->id,
            'user_id' => $agent2->id,
            'active_tickets_count' => 1,
            'is_available' => true,
        ]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT-PAYROLL',
            'name' => 'Payroll',
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-TAX',
            'name' => 'Tax Deduction Inquiry',
            'default_queue_id' => $queue->id,
        ]);

        $version = HrServiceVersion::create([
            'tenant_id' => $tenant->id,
            'hr_service_definition_id' => $service->id,
            'version_number' => 1,
            'effective_from' => '2026-01-01',
        ]);

        // Create routing rule
        HrServiceAssignmentRule::create([
            'tenant_id' => $tenant->id,
            'name' => 'Route Payroll to Payroll Queue',
            'priority' => 1,
            'hr_service_category_id' => $category->id,
            'target_queue_id' => $queue->id,
            'is_active' => true,
        ]);

        $requestService = app(ServiceRequestService::class);
        $request = $requestService->createRequest($employee, $service, ['subject' => 'Tax question']);
        $submitted = $requestService->submitRequest($request);

        // Under workload-based assignment, agent2 (1 active ticket) should be chosen over agent1 (5 tickets)
        $this->assertEquals($queue->id, $submitted->assigned_queue_id);
        $this->assertEquals($agent2->id, $submitted->assigned_user_id);
        $this->assertEquals(2, $member2->fresh()->active_tickets_count);
    }
}
