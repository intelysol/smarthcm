<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\EmployeeDirectoryService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDirectoryAndFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_search_and_filters(): void
    {
        $tenant = Tenant::factory()->create();
        $viewer = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'Main BU', 'code' => 'BU-01']);

        $deptSales = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Sales', 'department_code' => 'SALES']);
        $deptTech = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Technology', 'department_code' => 'TECH']);

        $desigDev = Designation::create(['tenant_id' => $tenant->id, 'designation_code' => 'SE', 'designation_name' => 'Software Engineer']);
        $desigRep = Designation::create(['tenant_id' => $tenant->id, 'designation_code' => 'SR', 'designation_name' => 'Sales Representative']);

        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $deptTech->id,
            'designation_id' => $desigDev->id,
            'employee_code' => 'TECH-001',
            'employee_number' => 'TECH-001',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'official_email' => 'alice@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $deptSales->id,
            'designation_id' => $desigRep->id,
            'employee_code' => 'SALES-001',
            'employee_number' => 'SALES-001',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'official_email' => 'bob@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = new EmployeeDirectoryService();

        // 1. Search by name keyword
        $results = $service->searchDirectory($viewer, ['search' => 'Alice']);
        $this->assertEquals(1, $results->total());
        $this->assertEquals('Alice', $results->items()[0]->first_name);

        // 2. Filter by department
        $resultsSales = $service->searchDirectory($viewer, ['department_id' => $deptSales->id]);
        $this->assertEquals(1, $resultsSales->total());
        $this->assertEquals('Bob', $resultsSales->items()[0]->first_name);

        // 3. Search all
        $all = $service->searchDirectory($viewer, []);
        $this->assertEquals(2, $all->total());
    }
}
