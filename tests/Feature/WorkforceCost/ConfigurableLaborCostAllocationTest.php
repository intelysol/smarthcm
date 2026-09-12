<?php

namespace Tests\Feature\WorkforceCost;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use App\Domains\WorkforceCost\Services\LaborCostAllocationService;
use App\Models\User;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

class ConfigurableLaborCostAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_labor_cost_allocation_rule_and_execution(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Core BU',
            'code' => 'CBU-' . Str::random(4),
        ]);
        $deptA = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_name' => 'Engineering',
            'department_code' => 'ENG-' . Str::random(4),
        ]);
        $deptB = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_name' => 'Operations',
            'department_code' => 'OPS-' . Str::random(4),
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $allocService = app(LaborCostAllocationService::class);

        // 1. Validation test: rule with invalid sum (e.g. 70 + 40 = 110%) throws exception
        $this->expectException(InvalidArgumentException::class);
        $allocService->createRule($tenant->id, [
            'rule_name' => 'Invalid Allocation Rule',
            'allocation_method' => 'percentage',
            'source_type' => 'department',
            'source_id' => $deptA->id,
            'targets' => [
                ['target_department_id' => $deptA->id, 'percentage' => 70],
                ['target_department_id' => $deptB->id, 'percentage' => 40],
            ],
        ]);
    }

    public function test_valid_rule_allocates_correctly_with_audit_trail(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Corporate BU',
            'code' => 'CRBU-' . Str::random(4),
        ]);
        $deptA = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_name' => 'Engineering',
            'department_code' => 'ENG-' . Str::random(4),
        ]);
        $deptB = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_name' => 'Marketing',
            'department_code' => 'MKT-' . Str::random(4),
        ]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $deptA->id,
            'employee_code' => 'EMP-ALC-02',
            'employee_number' => '300302',
            'first_name' => 'Usman',
            'last_name' => 'Qadir',
            'official_email' => 'usman.q@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $allocService = app(LaborCostAllocationService::class);
        $aggService = app(LaborCostAggregationService::class);

        // Create valid 60% / 40% rule
        $rule = $allocService->createRule($tenant->id, [
            'rule_name' => 'Engineering Split 60/40',
            'allocation_method' => 'percentage',
            'source_type' => 'department',
            'source_id' => $deptA->id,
            'targets' => [
                ['target_department_id' => $deptA->id, 'percentage' => 60],
                ['target_department_id' => $deptB->id, 'percentage' => 40],
            ],
            'priority' => 1,
        ]);

        $this->assertNotNull($rule);

        // Create a $10,000 cost line
        $line = $aggService->createCostLine($tenant->id, [
            'employee_id' => $employee->id,
            'department_id' => $deptA->id,
            'cost_category' => 'direct_labor',
            'component_type' => 'BASE_PAY',
            'cost_date' => '2026-10-20',
            'amount' => 10000.00,
        ]);

        // Allocate
        $allocations = $allocService->allocateCostLine($line, $rule);
        $this->assertCount(2, $allocations);

        $this->assertDatabaseHas('hcm_workforce_cost_allocations', [
            'tenant_id' => $tenant->id,
            'cost_line_id' => $line->id,
            'target_department_id' => $deptA->id,
            'allocated_percentage' => 60.00,
            'allocated_amount' => 6000.00,
        ]);

        $this->assertDatabaseHas('hcm_workforce_cost_allocations', [
            'tenant_id' => $tenant->id,
            'cost_line_id' => $line->id,
            'target_department_id' => $deptB->id,
            'allocated_percentage' => 40.00,
            'allocated_amount' => 4000.00,
        ]);

        // Verify audit entry
        $this->assertDatabaseHas('hcm_workforce_cost_audits', [
            'tenant_id' => $tenant->id,
            'action_type' => 'cost_allocated',
            'entity_id' => $line->id,
        ]);
    }
}