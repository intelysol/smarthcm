<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Enums\BulkOperationStatus;
use App\Domains\WorkforceAdmin\Services\BulkOperationService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkOperationDryRunAndExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_operation_dry_run_validation_approval_and_execution(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $bu = \App\Domains\Organization\Models\BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'BU-OPS',
            'name' => 'Operations Unit',
        ]);

        $deptNew = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'SALES-OPS',
            'department_name' => 'Sales Operations',
        ]);

        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BLK-001',
            'employee_number' => 'EMP-BLK-001',
            'first_name' => 'Pam',
            'last_name' => 'Beesly',
            'official_email' => 'pam@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BLK-002',
            'employee_number' => 'EMP-BLK-002',
            'first_name' => 'Ryan',
            'last_name' => 'Howard',
            'official_email' => 'ryan@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $bulkService = app(BulkOperationService::class);

        // 1. Create Draft Bulk Operation
        $bulkOp = $bulkService->createBulkOperation([
            'operation_type' => 'bulk_department_update',
            'reason' => 'Corporate reorg into central Sales Operations unit.',
            'effective_date' => '2026-10-01',
            'proposed_changes' => [
                'department_id' => $deptNew->id,
            ],
            'employee_ids' => [$emp1->id, $emp2->id],
        ], $user);

        $this->assertEquals(BulkOperationStatus::DRAFT, $bulkOp->status);
        $this->assertEquals(2, $bulkOp->total_records);
        $this->assertEquals(2, $bulkOp->items()->count());

        Carbon::setTestNow('2026-09-01 11:00:00');

        // 2. Validate & Dry Run
        $validation = $bulkService->validateAndDryRun($bulkOp);
        $this->assertEquals(BulkOperationStatus::DRY_RUN_READY, $bulkOp->fresh()->status);
        $this->assertEquals(2, $validation->valid_count);
        $this->assertEquals(0, $validation->error_count);
        $this->assertContains('core_hr', $validation->impacted_domains);
        $this->assertContains('payroll', $validation->impacted_domains);

        Carbon::setTestNow('2026-09-01 12:00:00');

        // 3. Approve Bulk Operation
        $bulkService->approveBulkOperation($bulkOp, $approver);
        $this->assertEquals(BulkOperationStatus::APPROVED, $bulkOp->fresh()->status);
        $this->assertEquals($approver->id, $bulkOp->fresh()->approved_by);

        Carbon::setTestNow('2026-09-01 13:00:00');

        // 4. Execute Bulk Operation
        $executed = $bulkService->executeBulkOperation($bulkOp, $user);
        $this->assertEquals(BulkOperationStatus::COMPLETED, $executed->fresh()->status);
        $this->assertEquals(2, $executed->fresh()->successful_records);

        // Verify Core HR employees received department change
        $this->assertEquals($deptNew->id, $emp1->fresh()->department_id);
        $this->assertEquals($deptNew->id, $emp2->fresh()->department_id);
    }
}
