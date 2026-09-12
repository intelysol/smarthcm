<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Models\OpsQueue;
use App\Domains\WorkforceAdmin\Services\HrOperationsDashboardService;
use App\Domains\WorkforceAdmin\Services\OperationsQueueService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceAdminDashboardAndQueuesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_service_aggregates_comprehensive_metrics(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'HQ Unit',
            'code' => 'BU-HQ',
        ]);

        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-01',
            'department_name' => 'HR Operations',
        ]);

        $designation = Designation::create([
            'tenant_id' => $tenant->id,
            'designation_code' => 'DES-01',
            'designation_name' => 'HR Specialist',
        ]);

        // Manager Employee
        $manager = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-MGR',
            'employee_number' => 'EMP-MGR',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'official_email' => 'michael.mgr@example.com',
            'department_id' => $dept->id,
            'designation_id' => $designation->id,
            'employment_status' => 'active',
            'joining_date' => '2020-01-01',
        ]);
        $manager->update(['reporting_manager_id' => $manager->id]);

        // Complete employee joining soon
        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-001',
            'employee_number' => 'EMP-001',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'official_email' => 'alice@example.com',
            'department_id' => $dept->id,
            'designation_id' => $designation->id,
            'reporting_manager_id' => $manager->id,
            'employment_status' => 'active',
            'joining_date' => '2026-09-10',
        ]);

        // Incomplete employee (missing department, designation, manager)
        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-002',
            'employee_number' => 'EMP-002',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'official_email' => 'bob@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
            'department_id' => null,
            'designation_id' => null,
            'reporting_manager_id' => null,
        ]);

        $dashboardService = app(HrOperationsDashboardService::class);
        $summary = $dashboardService->getExecutiveSummary($tenant->id);

        $this->assertEquals(3, $summary['workforce']['total_employees']);
        $this->assertEquals(3, $summary['workforce']['active_employees']);
        $this->assertEquals(1, $summary['workforce']['joining_soon']);
        $this->assertEquals(1, $summary['workforce']['incomplete_records']);
        $this->assertArrayHasKey('compliance', $summary);
        $this->assertArrayHasKey('documents', $summary);
        $this->assertArrayHasKey('payroll', $summary);
        $this->assertArrayHasKey('benefits', $summary);
    }

    public function test_queue_lifecycle_enqueue_assign_and_complete(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-003',
            'employee_number' => 'EMP-003',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'official_email' => 'charlie@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $queue = OpsQueue::create([
            'tenant_id' => $tenant->id,
            'code' => 'NEW_HIRE_QUEUE',
            'name' => 'New Hire Verification Queue',
            'category' => 'lifecycle',
            'default_priority' => 'high',
            'target_sla_hours' => 12,
        ]);

        $queueService = app(OperationsQueueService::class);

        // 1. Enqueue
        $item = $queueService->enqueueItem($queue, [
            'title' => 'Verify passport copy for new hire',
            'entity_type' => 'Employee',
            'entity_id' => $employee->id,
            'employee_id' => $employee->id,
        ]);

        $this->assertEquals('pending', $item->status);
        $this->assertEquals('high', $item->priority);
        $this->assertNotNull($item->due_at);

        // 2. Assign
        $assigned = $queueService->assignItem($item, $user, 'HR Operations Team');
        $this->assertEquals('assigned', $assigned->status);
        $this->assertEquals($user->id, $assigned->assigned_to);
        $this->assertEquals('HR Operations Team', $assigned->assigned_team);
        $this->assertNotNull($assigned->first_responded_at);

        // 3. Complete
        $completed = $queueService->completeItem($assigned, $user);
        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->resolved_at);
    }
}
