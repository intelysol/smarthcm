<?php

namespace Tests\Feature\Payroll;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Enums\ProrationMethod;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\CompensationService;
use App\Domains\Payroll\Services\PayrollCalculationEngine;
use App\Domains\Payroll\Services\PayrollPeriodService;
use App\Domains\Payroll\Services\PayrollRunService;
use App\Domains\Payroll\Services\ProrationService;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\PayrollDefaultDataSeeder;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollCalculationAndProrationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_mid_period_hire_calendar_days_proration(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        // Employee joined mid-month on 2026-09-16 (15 days out of 30 in September)
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'joining_date' => '2026-09-16',
        ]);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $periodService = app(PayrollPeriodService::class);
        $period = $periodService->createPeriod($tenant->id, [
            'period_name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);

        $prorationService = app(ProrationService::class);
        $proration = $prorationService->calculateProration($employee, $period, ProrationMethod::CALENDAR_DAYS);

        $this->assertTrue($proration['is_prorated']);
        $this->assertEquals(15, $proration['eligible_days']);
        $this->assertEquals(30, $proration['total_days']);
        $this->assertEquals(0.5, $proration['factor']);
    }

    public function test_deterministic_calculation_snapshot_and_idempotency(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'joining_date' => '2026-01-01',
        ]);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        // Assign Salary: $6,000 Base
        $compService = app(CompensationService::class);
        $structure = CompensationStructure::query()->where('tenant_id', $tenant->id)->first();
        $compService->assignCompensation($employee, [
            'compensation_structure_id' => $structure->id,
            'base_salary' => 6000.00,
            'effective_from' => '2026-01-01',
        ], []);

        $periodService = app(PayrollPeriodService::class);
        $period = $periodService->createPeriod($tenant->id, [
            'period_name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);

        $runService = app(PayrollRunService::class);
        $run = $runService->createRun($period, ['name' => 'September Regular Run']);

        $engine = app(PayrollCalculationEngine::class);

        // Calculation 1
        $snap1 = $engine->calculateEmployeePayroll($run, $employee);
        $this->assertEquals(6000.00, (float) $snap1->gross_pay);
        $this->assertGreaterThan(0, (float) $snap1->net_pay);
        $key1 = $snap1->idempotency_key;

        // Re-calculation for same employee in same run produces identical results
        $snap2 = $engine->calculateEmployeePayroll($run, $employee);
        $this->assertEquals($key1, $snap2->idempotency_key);
        $this->assertEquals((float) $snap1->net_pay, (float) $snap2->net_pay);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Charlie',
            'last_name' => 'Davis',
            'official_email' => 'charlie.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
