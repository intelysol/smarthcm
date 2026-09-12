<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Models\OpsQueue;
use App\Domains\WorkforceAdmin\Services\HrOperationsDashboardService;
use App\Domains\WorkforceAdmin\Services\OperationsQueueService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrOperationsDashboardAndQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_aggregation_and_operational_queue_workflow(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        // Create Employees
        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-WA-001',
            'employee_number' => 'EMP-WA-001',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'official_email' => 'michael@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-09-10', // joining soon
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-WA-002',
            'employee_number' => 'EMP-WA-002',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'official_email' => 'dwight@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
            // Missing department/designation/manager -> incomplete profile
        ]);

        // 1. Test Dashboard Aggregation
        $dashboardService = app(HrOperationsDashboardService::class);
        $summary = $dashboardService->getExecutiveSummary($tenant->id);

        $this->assertEquals(2, $summary['workforce']['total_employees']);
        $this->assertEquals(2, $summary['workforce']['active_employees']);
        $this->assertEquals(1, $summary['workforce']['joining_soon']);
        $this->assertEquals(2, $summary['workforce']['incomplete_records']);

        // 2. Test Operational Queue Setup & Item Lifecycle
        $queue = OpsQueue::create([
            'tenant_id' => $tenant->id,
            'code' => 'NEW-HIRE-QUEUE',
            'name' => 'New Hire Onboarding Queue',
            'category' => 'lifecycle',
            'default_priority' => 'high',
            'target_sla_hours' => 12,
            'is_active' => true,
        ]);

        $queueService = app(OperationsQueueService::class);

        // Enqueue item
        $item = $queueService->enqueueItem($queue, [
            'title' => 'Verify Michael Scott Onboarding Checklist',
            'entity_type' => 'Employee',
            'entity_id' => $emp1->id,
            'employee_id' => $emp1->id,
        ]);

        $this->assertDatabaseHas('hcm_ops_queue_items', [
            'id' => $item->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);
        $this->assertNotNull($item->due_at);

        Carbon::setTestNow('2026-09-01 11:00:00');

        // Assign item
        $queueService->assignItem($item, $user, 'HR Operations Team');
        $this->assertEquals('assigned', $item->fresh()->status);
        $this->assertEquals($user->id, $item->fresh()->assigned_to);
        $this->assertNotNull($item->fresh()->first_responded_at);

        Carbon::setTestNow('2026-09-01 12:00:00');

        // Complete item
        $queueService->completeItem($item, $user);
        $this->assertEquals('completed', $item->fresh()->status);
        $this->assertNotNull($item->fresh()->resolved_at);
    }
}
