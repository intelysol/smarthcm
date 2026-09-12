<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\PeopleSearchService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeopleSearchAndAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_people_search_and_autocomplete(): void
    {
        $tenant = Tenant::factory()->create();
        $viewer = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'Main BU', 'code' => 'BU-01']);

        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Human Resources', 'department_code' => 'HR']);

        $activeEmp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'HR-007',
            'employee_number' => 'HR-007',
            'first_name' => 'James',
            'last_name' => 'Bond',
            'official_email' => 'james.bond@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $inactiveEmp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'HR-008',
            'employee_number' => 'HR-008',
            'first_name' => 'James',
            'last_name' => 'Terminated',
            'official_email' => 'james.term@example.com',
            'employment_status' => 'terminated',
            'joining_date' => now()->subYears(2)->toDateString(),
        ]);

        $service = new PeopleSearchService();

        // 1. Search by name (only active returned)
        $results = $service->search($viewer, 'James');
        $this->assertCount(1, $results);
        $this->assertEquals('James Bond', $results[0]['name']);
        $this->assertEquals('HR-007', $results[0]['employee_code']);

        // 2. Autocomplete
        $auto = $service->autocomplete($viewer, 'Bond');
        $this->assertCount(1, $auto);
        $this->assertEquals('James Bond', $auto[0]['name']);
    }
}
