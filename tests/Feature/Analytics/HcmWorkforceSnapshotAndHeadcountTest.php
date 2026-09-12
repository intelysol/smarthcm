<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Services\HcmWorkforceAnalyticsService;
use App\Domains\Analytics\Services\HcmWorkforceSnapshotService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmWorkforceSnapshotAndHeadcountTest extends TestCase
{
    use RefreshDatabase;

    public function test_workforce_snapshots_and_headcount_calculations(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);
        $branch = Branch::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_name' => 'HQ Branch', 'branch_code' => 'BR-01']);

        // Create Active Full-time Employee
        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'branch_id' => $branch->id,
            'employee_code' => 'EMP-01',
            'employee_number' => 'EMP-01',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'john.doe@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // Create Active Second Employee
        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'branch_id' => $branch->id,
            'employee_code' => 'EMP-02',
            'employee_number' => 'EMP-02',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'official_email' => 'jane.smith@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-02-01',
        ]);

        // Create Terminated Employee
        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'branch_id' => $branch->id,
            'employee_code' => 'EMP-03',
            'employee_number' => 'EMP-03',
            'first_name' => 'Bob',
            'last_name' => 'Taylor',
            'official_email' => 'bob.taylor@example.com',
            'employment_status' => 'terminated',
            'joining_date' => '2025-01-01',
            'termination_date' => '2026-01-15',
        ]);

        $snapshotService = app(HcmWorkforceSnapshotService::class);
        $analyticsService = app(HcmWorkforceAnalyticsService::class);

        // 1. Generate Daily Snapshot as of 2026-03-01
        $snapshot = $snapshotService->buildSnapshot($tenant->id, '2026-03-01', 'daily');

        $this->assertEquals(2, $snapshot->headcount_active);
        $this->assertEquals(2, $snapshot->headcount_total);
        $this->assertEquals(2.0, (float) $snapshot->fte_total);
        $this->assertArrayHasKey('Engineering', $snapshot->by_department);

        // 2. Real-time Summary
        $summary = $analyticsService->getHeadcountSummary($tenant->id, '2026-03-01');
        $this->assertEquals(2, $summary['active_headcount']);
        $this->assertEquals(2, $summary['total_headcount']);
    }
}
