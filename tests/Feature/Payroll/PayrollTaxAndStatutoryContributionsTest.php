<?php

namespace Tests\Feature\Payroll;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\CompensationComponent;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\PayrollTaxRule;
use App\Domains\Payroll\Models\PayrollTaxRuleVersion;
use App\Domains\Payroll\Services\CompensationService;
use App\Domains\Payroll\Services\PayrollCalculationEngine;
use App\Domains\Payroll\Services\PayrollPeriodService;
use App\Domains\Payroll\Services\PayrollRunService;
use App\Domains\Payroll\Services\TaxCalculationService;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\PayrollDefaultDataSeeder;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTaxAndStatutoryContributionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_progressive_tax_brackets_calculation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $periodService = app(PayrollPeriodService::class);
        $period = $periodService->createPeriod($tenant->id, [
            'period_name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);

        $taxService = app(TaxCalculationService::class);

        // Test with $6,000 taxable income:
        // Exemption = $1,000 -> Net Taxable = $5,000
        // Bracket 1: $0 - $2,000 @ 5% = $100
        // Bracket 2: $2,000 - $5,000 @ 10% = $300
        // Total Tax Expected = $400.00
        $result = $taxService->calculateTax($employee, $period, 6000.00);

        $this->assertEquals(6000.00, $result['taxable_income']);
        $this->assertEquals(1000.00, $result['exemptions']);
        $this->assertEquals(400.00, $result['net_tax_deducted']);
        $this->assertNotEmpty($result['tax_bracket_breakdown']);
    }

    public function test_employer_statutory_contribution_pension_cost(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $compService = app(CompensationService::class);
        $structure = CompensationStructure::query()->where('tenant_id', $tenant->id)->first();
        $pensionER = CompensationComponent::query()->where('tenant_id', $tenant->id)->where('code', 'PENSION_ER')->first();

        // Assign $5,000 Base + 7.5% Employer Pension ($375)
        $compService->assignCompensation($employee, [
            'compensation_structure_id' => $structure->id,
            'base_salary' => 5000.00,
            'effective_from' => '2026-01-01',
        ], [
            [
                'component_id' => $pensionER->id,
                'calculation_type' => 'percentage_of_basic',
                'percentage' => 7.50,
                'amount' => 375.00,
            ],
        ]);

        $periodService = app(PayrollPeriodService::class);
        $period = $periodService->createPeriod($tenant->id, [
            'period_name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);

        $runService = app(PayrollRunService::class);
        $run = $runService->createRun($period, ['name' => 'September Run']);

        $engine = app(PayrollCalculationEngine::class);
        $snapshot = $engine->calculateEmployeePayroll($run, $employee);

        // Employer cost = 375
        $this->assertEquals(375.00, (float) $snapshot->total_employer_cost);

        $this->assertDatabaseHas('payroll_employer_contributions', [
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'contribution_code' => 'PENSION_ER',
            'amount' => 375.00,
        ]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'official_email' => 'emma.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
