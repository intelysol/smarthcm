<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Models\OpsDataQualityRule;
use App\Domains\WorkforceAdmin\Models\OpsReconciliationRule;
use App\Domains\WorkforceAdmin\Services\CrossDomainReconciliationService;
use App\Domains\WorkforceAdmin\Services\HrDataQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceAdminDataQualityAndReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_quality_scan_and_scoring(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Engineering Unit',
            'code' => 'BU-ENG',
        ]);

        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-ENG',
            'department_name' => 'Engineering',
        ]);

        // 1 valid employee, 1 invalid employee (missing department)
        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DQ-01',
            'employee_number' => 'EMP-DQ-01',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'official_email' => 'michael@example.com',
            'department_id' => $dept->id,
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DQ-02',
            'employee_number' => 'EMP-DQ-02',
            'first_name' => 'Ryan',
            'last_name' => 'Howard',
            'official_email' => 'ryan@example.com',
            'department_id' => null,
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        OpsDataQualityRule::create([
            'tenant_id' => $tenant->id,
            'rule_code' => 'MISSING_DEPARTMENT',
            'name' => 'Department Required',
            'category' => 'completeness',
            'domain' => 'core_hr',
            'severity' => 'error',
            'is_active' => true,
        ]);

        $dqService = app(HrDataQualityService::class);
        $run = $dqService->runQualityScan($tenant->id);

        $this->assertEquals(2, $run->total_records_scanned);
        $this->assertEquals(1, $run->total_violations_found);
        $this->assertEquals(50.00, (float) $run->overall_score);
        $this->assertDatabaseHas('hcm_ops_data_quality_results', [
            'run_id' => $run->id,
            'status' => 'open',
        ]);
    }

    public function test_cross_domain_reconciliation_detects_missing_payroll(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $empWithPayroll = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REC-01',
            'employee_number' => 'EMP-REC-01',
            'first_name' => 'Stanley',
            'last_name' => 'Hudson',
            'official_email' => 'stanley@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $empMissingPayroll = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REC-02',
            'employee_number' => 'EMP-REC-02',
            'first_name' => 'Phyllis',
            'last_name' => 'Vance',
            'official_email' => 'phyllis@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        EmployeeCompensation::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $empWithPayroll->id,
            'base_salary' => 85000,
            'currency' => 'USD',
            'pay_frequency' => 'monthly',
            'effective_from' => now()->toDateString(),
            'status' => 'active',
        ]);

        $rule = OpsReconciliationRule::create([
            'tenant_id' => $tenant->id,
            'code' => 'RECON_CORE_HR_PAYROLL',
            'name' => 'Core HR to Payroll Compensation Reconciliation',
            'source_domain' => 'core_hr',
            'target_domain' => 'payroll',
            'is_active' => true,
        ]);

        $reconService = app(CrossDomainReconciliationService::class);
        $results = $reconService->reconcile($rule);

        $this->assertCount(2, $results);

        $matched = collect($results)->firstWhere('employee_id', $empWithPayroll->id);
        $missing = collect($results)->firstWhere('employee_id', $empMissingPayroll->id);

        $this->assertEquals('matched', $matched->reconciliation_status);
        $this->assertEquals('missing', $missing->reconciliation_status);
    }
}
