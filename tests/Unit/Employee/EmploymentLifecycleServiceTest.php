<?php

namespace Tests\Unit\Employee;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Services\EmploymentLifecycleService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmploymentLifecycleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_rehire_creates_a_new_employment_without_overwriting_history(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'employee_number' => 'EMP-001', 'employee_code' => 'EMP-001', 'first_name' => 'Ayesha', 'last_name' => 'Khan', 'employment_status' => 'archived', 'joining_date' => '2024-01-01']);
        $employment = app(EmploymentLifecycleService::class)->rehire($employee, ['company_id' => $company->id, 'start_date' => '2026-08-20']);

        $this->assertDatabaseHas('employments', ['id' => $employment->id, 'employee_id' => $employee->id, 'status' => 'active']);
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'current_employment_id' => $employment->id, 'employment_status' => 'active']);
    }
}
