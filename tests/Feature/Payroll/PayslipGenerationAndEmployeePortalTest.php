<?php

namespace Tests\Feature\Payroll;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\CompensationService;
use App\Domains\Payroll\Services\PayrollPeriodService;
use App\Domains\Payroll\Services\PayrollRunService;
use App\Domains\Payroll\Services\PayslipService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PayrollDefaultDataSeeder;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayslipGenerationAndEmployeePortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_payslip_generation_publishing_and_ytd_accumulation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = $this->createEmployee($tenant->id, $company->id, ['user_id' => $user->id]);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $compService = app(CompensationService::class);
        $structure = CompensationStructure::query()->where('tenant_id', $tenant->id)->first();
        $compService->assignCompensation($employee, [
            'compensation_structure_id' => $structure->id,
            'base_salary' => 5000.00,
            'effective_from' => '2026-01-01',
        ], []);

        $periodService = app(PayrollPeriodService::class);
        $period = $periodService->createPeriod($tenant->id, [
            'period_name' => 'January 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

        $runService = app(PayrollRunService::class);
        $run = $runService->createRun($period, ['name' => 'January Run']);
        $runService->calculateRun($run);

        $payslipService = app(PayslipService::class);
        $payslips = $payslipService->generatePayslipsForRun($run);

        $this->assertCount(1, $payslips);
        $payslip = $payslips->first();
        $this->assertEquals(5000.00, (float) $payslip->gross_pay);
        $this->assertFalse((bool) $payslip->is_published);

        // Publish payslips
        $payslipService->publishPayslipsForRun($run);
        $this->assertTrue((bool) $payslip->fresh()->is_published);

        // Employee accesses self-service portal
        $empPayslips = $payslipService->getEmployeePayslips($employee);
        $this->assertCount(1, $empPayslips);
        $this->assertEquals($payslip->payslip_number, $empPayslips->first()->payslip_number);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'official_email' => 'grace.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
