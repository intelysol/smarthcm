<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Enums\BulkOperationStatus;
use App\Domains\WorkforceAdmin\Services\BulkOperationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceAdminBulkOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_operation_full_governed_lifecycle(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Commercial Division',
            'code' => 'BU-COMM',
        ]);

        $dept1 = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-01',
            'department_name' => 'Sales',
        ]);

        $dept2 = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-02',
            'department_name' => 'Marketing',
        ]);

        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BLK-01',
            'employee_number' => 'EMP-BLK-01',
            'first_name' => 'Jim',
            'last_name' => 'Halpert',
            'official_email' => 'jim@example.com',
            'department_id' => $dept1->id,
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-BLK-02',
            'employee_number' => 'EMP-BLK-02',
            'first_name' => 'Pam',
            'last_name' => 'Beesly',
            'official_email' => 'pam@example.com',
            'department_id' => $dept1->id,
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $bulkService = app(BulkOperationService::class);

        // 1. Create Draft
        $bulkOp = $bulkService->createBulkOperation([
            'operation_type' => 'bulk_department_update',
            'reason' => 'Annual organizational restructuring',
            'proposed_changes' => ['department_id' => $dept2->id],
            'employee_ids' => [$emp1->id, $emp2->id],
        ], $user);

        $this->assertEquals(BulkOperationStatus::DRAFT, $bulkOp->status);
        $this->assertEquals(2, $bulkOp->items()->count());

        // 2. Validate & Dry Run
        $validation = $bulkService->validateAndDryRun($bulkOp);
        $this->assertEquals(2, $validation->valid_count);
        $this->assertEquals(0, $validation->error_count);
        $this->assertTrue(in_array('payroll', $validation->impacted_domains));

        $bulkOp->refresh();
        $this->assertEquals(BulkOperationStatus::DRY_RUN_READY, $bulkOp->status);

        // 3. Approve
        $approved = $bulkService->approveBulkOperation($bulkOp, $user);
        $this->assertEquals(BulkOperationStatus::APPROVED, $approved->status);
        $this->assertEquals($user->id, $approved->approved_by);

        // 4. Execute
        $executed = $bulkService->executeBulkOperation($approved, $user);
        $this->assertEquals(BulkOperationStatus::COMPLETED, $executed->status);
        $this->assertEquals(2, $executed->successful_records);

        // Verify domain record update
        $emp1->refresh();
        $emp2->refresh();
        $this->assertEquals($dept2->id, $emp1->department_id);
        $this->assertEquals($dept2->id, $emp2->department_id);
    }
}
