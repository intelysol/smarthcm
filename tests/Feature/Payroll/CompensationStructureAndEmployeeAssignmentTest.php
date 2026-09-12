<?php

namespace Tests\Feature\Payroll;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\CompensationComponent;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\Payroll\Services\CompensationService;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\PayrollDefaultDataSeeder;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompensationStructureAndEmployeeAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_compensation_structure_creation_and_component_composition(): void
    {
        $tenant = Tenant::factory()->create();
        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $structure = CompensationStructure::query()->where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($structure);
        $this->assertGreaterThan(0, $structure->structureComponents()->count());

        $basic = CompensationComponent::query()->where('tenant_id', $tenant->id)->where('code', 'BASIC')->first();
        $this->assertTrue($basic->isEarning());
        $this->assertFalse($basic->isDeduction());
    }

    public function test_employee_compensation_assignment_and_effective_dating(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $compService = app(CompensationService::class);
        $structure = CompensationStructure::query()->where('tenant_id', $tenant->id)->first();
        $housing = CompensationComponent::query()->where('tenant_id', $tenant->id)->where('code', 'HOUSING')->first();

        // 1. Initial Salary Assignment: $5,000 base + 30% Housing ($1,500) -> Gross $6,500
        $initialComp = $compService->assignCompensation($employee, [
            'compensation_structure_id' => $structure->id,
            'base_salary' => 5000.00,
            'effective_from' => '2026-01-01',
            'currency' => 'USD',
        ], [
            [
                'component_id' => $housing->id,
                'calculation_type' => 'percentage_of_basic',
                'percentage' => 30.00,
                'amount' => 1500.00,
            ],
        ]);

        $this->assertEquals(5000.00, (float) $initialComp->base_salary);
        $this->assertEquals(6500.00, (float) $initialComp->gross_salary);

        // 2. Promotion / Merit Increase on 2026-07-01: $7,000 base + 30% Housing ($2,100) -> Gross $9,100
        $revisedComp = $compService->assignCompensation($employee, [
            'compensation_structure_id' => $structure->id,
            'base_salary' => 7000.00,
            'effective_from' => '2026-07-01',
            'reason_for_change' => 'promotion',
            'currency' => 'USD',
        ], [
            [
                'component_id' => $housing->id,
                'calculation_type' => 'percentage_of_basic',
                'percentage' => 30.00,
                'amount' => 2100.00,
            ],
        ]);

        // Historical query on 2026-03-15 returns initial compensation
        $activeInMarch = $compService->getActiveCompensationForDate($employee, '2026-03-15');
        $this->assertEquals(5000.00, (float) $activeInMarch->base_salary);

        // Historical query on 2026-08-01 returns revised compensation
        $activeInAugust = $compService->getActiveCompensationForDate($employee, '2026-08-01');
        $this->assertEquals(7000.00, (float) $activeInAugust->base_salary);
        $this->assertEquals(9100.00, (float) $activeInAugust->gross_salary);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'official_email' => 'alice.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
