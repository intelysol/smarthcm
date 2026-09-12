<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Models\OpsDataQualityRule;
use App\Domains\WorkforceAdmin\Models\OpsReconciliationRule;
use App\Domains\WorkforceAdmin\Services\CrossDomainReconciliationService;
use App\Domains\WorkforceAdmin\Services\HrDataQualityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrDataQualityAndReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_quality_scan_and_cross_domain_reconciliation(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $bu = \App\Domains\Organization\Models\BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'BU-SALES',
            'name' => 'Sales BU',
        ]);

        $dept = \App\Domains\Organization\Models\Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'SALES',
            'department_name' => 'Sales',
        ]);

        $mgr = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-MGR-001',
            'employee_number' => 'EMP-MGR-001',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'official_email' => 'mscott@example.com',
            'employment_status' => 'active',
            'joining_date' => '2023-01-01',
        ]);

        // Employee 1: Clean record with payroll setup
        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DQR-001',
            'employee_number' => 'EMP-DQR-001',
            'first_name' => 'Stanley',
            'last_name' => 'Hudson',
            'official_email' => 'stanley@example.com',
            'employment_status' => 'active',
            'joining_date' => '2024-01-01',
            'department_id' => $dept->id,
            'reporting_manager_id' => $mgr->id,
        ]);

        EmployeeCompensation::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $emp1->id,
            'base_salary' => 85000.00,
            'pay_frequency' => 'monthly',
            'currency' => 'USD',
            'effective_from' => '2024-01-01',
        ]);

        // Employee 2: Incomplete record missing department & missing payroll
        $emp2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DQR-002',
            'employee_number' => 'EMP-DQR-002',
            'first_name' => 'Kevin',
            'last_name' => 'Malone',
            'official_email' => 'kevin@example.com',
            'employment_status' => 'active',
            'joining_date' => '2024-05-01',
            'department_id' => null, // violation
            'reporting_manager_id' => $mgr->id,
        ]);

        // 1. Setup Data Quality Rules
        OpsDataQualityRule::create([
            'tenant_id' => $tenant->id,
            'rule_code' => 'MISSING_DEPARTMENT',
            'name' => 'Department Assignment Mandatory',
            'category' => 'completeness',
            'domain' => 'core_hr',
            'severity' => 'warning',
            'is_active' => true,
        ]);

        $qualityService = app(HrDataQualityService::class);
        $run = $qualityService->runQualityScan($tenant->id);

        $this->assertEquals(3, $run->total_records_scanned);
        $this->assertEquals(2, $run->total_violations_found); // emp2 and mgr missing department
        $this->assertDatabaseHas('hcm_ops_data_quality_results', [
            'run_id' => $run->id,
            'employee_id' => $emp2->id,
        ]);

        // 2. Cross-Domain Reconciliation Check (Core HR <-> Payroll)
        $reconRule = OpsReconciliationRule::create([
            'tenant_id' => $tenant->id,
            'code' => 'RECON-HR-PAYROLL',
            'name' => 'Core HR to Payroll Compensation Reconciliation',
            'source_domain' => 'core_hr',
            'target_domain' => 'payroll',
            'is_active' => true,
        ]);

        $reconService = app(CrossDomainReconciliationService::class);
        $results = $reconService->reconcile($reconRule);

        $this->assertCount(3, $results);

        $matched = collect($results)->firstWhere('employee_id', $emp1->id);
        $missing = collect($results)->firstWhere('employee_id', $emp2->id);

        $this->assertEquals('matched', $matched->reconciliation_status);
        $this->assertEquals('missing', $missing->reconciliation_status);
    }
}
