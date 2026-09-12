<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\EmployeeProfileService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeProfileAggregationAndResilienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_header_and_resilient_aggregation(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'Main BU', 'code' => 'BU-01']);

        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);
        $desig = Designation::create(['tenant_id' => $tenant->id, 'designation_code' => 'SDEV', 'designation_name' => 'Senior Developer']);

        $manager = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-MGR-1',
            'employee_number' => 'EMP-MGR-1',
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'official_email' => 'sarah@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->subYears(3)->toDateString(),
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'reporting_manager_id' => $manager->id,
            'employee_code' => 'EMP-DEV-100',
            'employee_number' => 'EMP-DEV-100',
            'first_name' => 'John',
            'last_name' => 'Connor',
            'official_email' => 'john@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->subYears(1)->toDateString(),
            'confirmation_date' => now()->subMonths(6)->toDateString(),
        ]);

        $service = new EmployeeProfileService();

        // 1. Profile Header
        $header = $service->getProfileHeader($employee, $admin);
        $this->assertEquals('John Connor', $header['full_name']);
        $this->assertEquals('EMP-DEV-100', $header['employee_code']);
        $this->assertEquals('Senior Developer', $header['job_title']);
        $this->assertEquals('Engineering', $header['department']);
        $this->assertEquals('Sarah Connor', $header['manager']['name']);

        // 2. Resilient Profile Summary Aggregation
        $summary = $service->getProfileSummary($employee, $admin);
        $this->assertArrayHasKey('header', $summary);
        $this->assertArrayHasKey('sections', $summary);

        $this->assertEquals('available', $summary['sections']['employment']['status']);
        $this->assertEquals('available', $summary['sections']['documents']['status']);
        $this->assertEquals('available', $summary['sections']['learning']['status']);
        $this->assertEquals('available', $summary['sections']['payroll']['status']);
        $this->assertEquals('available', $summary['sections']['timeline']['status']);
    }
}
