<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Services\HcmCompensationAndPayrollAnalyticsService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmPayrollCompensationAndExpenseAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_salary_distribution_and_percentiles_calculation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Finance', 'department_code' => 'FIN']);

        $salaries = [30000, 40000, 50000, 60000, 70000, 80000, 90000, 100000];

        foreach ($salaries as $idx => $salary) {
            $emp = Employee::create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'department_id' => $dept->id,
                'employee_code' => "EMP-SAL-{$idx}",
                'employee_number' => "EMP-SAL-{$idx}",
                'first_name' => "Sal",
                'last_name' => "User {$idx}",
                'official_email' => "sal{$idx}@example.com",
                'employment_status' => 'active',
                'joining_date' => '2026-01-01',
            ]);

            EmployeeCompensation::create([
                'tenant_id' => $tenant->id,
                'employee_id' => $emp->id,
                'base_salary' => $salary,
                'currency' => 'USD',
                'pay_frequency' => 'monthly',
                'effective_from' => '2026-01-01',
                'is_active' => true,
            ]);
        }

        $service = app(HcmCompensationAndPayrollAnalyticsService::class);
        $distribution = $service->getSalaryDistribution($tenant->id);

        $this->assertEquals(8, $distribution['count']);
        $this->assertEquals(30000, $distribution['min']);
        $this->assertEquals(100000, $distribution['max']);
        $this->assertEquals(65000, $distribution['mean']);
        $this->assertGreaterThan(30000, $distribution['median']);
        $this->assertGreaterThan(0, $distribution['p25']);
        $this->assertGreaterThan(0, $distribution['p75']);
    }
}
