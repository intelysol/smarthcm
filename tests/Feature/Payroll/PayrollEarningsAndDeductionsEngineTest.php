<?php

namespace Tests\Feature\Payroll;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\CompensationComponent;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\PayrollAdjustment;
use App\Domains\Payroll\Models\PayrollArrear;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\CompensationService;
use App\Domains\Payroll\Services\PayrollAdjustmentService;
use App\Domains\Payroll\Services\PayrollCalculationEngine;
use App\Domains\Payroll\Services\PayrollPeriodService;
use App\Domains\Payroll\Services\PayrollRunService;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\PayrollDefaultDataSeeder;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollEarningsAndDeductionsEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_earnings_and_deductions_breakdown_with_arrears_and_adjustments(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $compService = app(CompensationService::class);
        $structure = CompensationStructure::query()->where('tenant_id', $tenant->id)->first();
        $housing = CompensationComponent::query()->where('tenant_id', $tenant->id)->where('code', 'HOUSING')->first();
        $pensionEE = CompensationComponent::query()->where('tenant_id', $tenant->id)->where('code', 'PENSION_EE')->first();

        // Assign: $4,000 Base + 30% Housing ($1,200) + 5% Pension Deduction ($200)
        $compService->assignCompensation($employee, [
            'compensation_structure_id' => $structure->id,
            'base_salary' => 4000.00,
            'effective_from' => '2026-01-01',
        ], [
            [
                'component_id' => $housing->id,
                'calculation_type' => 'percentage_of_basic',
                'percentage' => 30.00,
                'amount' => 1200.00,
            ],
            [
                'component_id' => $pensionEE->id,
                'calculation_type' => 'percentage_of_basic',
                'percentage' => 5.00,
                'amount' => 200.00,
            ],
        ]);

        $periodService = app(PayrollPeriodService::class);
        $period = $periodService->createPeriod($tenant->id, [
            'period_name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);

        // Add Retroactive Arrear: $300 difference
        $adjService = app(PayrollAdjustmentService::class);
        $adjService->createSalaryArrear(
            $employee,
            'July Merit Arrear',
            '2026-07-01',
            '2026-07-31',
            4000.00,
            4300.00,
            $period
        );

        $runService = app(PayrollRunService::class);
        $run = $runService->createRun($period, ['name' => 'September Regular']);

        $engine = app(PayrollCalculationEngine::class);
        $snapshot = $engine->calculateEmployeePayroll($run, $employee);

        // Expected Gross = 4000 (Base) + 1200 (Housing) + 300 (Arrear) = 5500
        $this->assertEquals(5500.00, (float) $snapshot->gross_pay);

        // Check earnings itemization
        $this->assertDatabaseHas('payroll_earnings', [
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'earning_code' => 'BASIC',
            'amount' => 4000.00,
        ]);
        $this->assertDatabaseHas('payroll_earnings', [
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'earning_code' => 'HOUSING',
            'amount' => 1200.00,
        ]);
        $this->assertDatabaseHas('payroll_earnings', [
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'earning_code' => 'ARREAR',
            'amount' => 300.00,
        ]);

        // Check pension deduction itemization
        $this->assertDatabaseHas('payroll_deductions', [
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'deduction_code' => 'PENSION_EE',
            'amount' => 200.00,
        ]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'David',
            'last_name' => 'Evans',
            'official_email' => 'david.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
