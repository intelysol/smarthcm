<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeCertification;
use App\Domains\EmployeeProfile\Services\EmployeeTimelineService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTimelineAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_aggregation_chronology(): void
    {
        $tenant = Tenant::factory()->create();
        $viewer = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'Main BU', 'code' => 'BU-01']);

        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Marketing', 'department_code' => 'MKT']);
        $desig = Designation::create(['tenant_id' => $tenant->id, 'designation_code' => 'BS', 'designation_name' => 'Brand Strategist']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'employee_code' => 'MKT-001',
            'employee_number' => 'MKT-001',
            'first_name' => 'Diana',
            'last_name' => 'Prince',
            'joining_date' => '2024-01-15',
            'confirmation_date' => '2024-07-15',
            'employment_status' => 'active',
        ]);

        EmployeeCertification::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'name' => 'Certified Digital Marketing Professional',
            'issuing_organization' => 'DMI',
            'issue_date' => '2025-03-10',
        ]);

        $service = new EmployeeTimelineService();
        $events = $service->getTimelineEvents($employee, $viewer);

        $this->assertCount(3, $events);
        // Latest first: Cert (2025-03-10), Confirmation (2024-07-15), Joining (2024-01-15)
        $this->assertEquals('Professional Certification Completed', $events[0]['title']);
        $this->assertEquals('2025-03-10', $events[0]['date']);

        $this->assertEquals('Employment Confirmed', $events[1]['title']);
        $this->assertEquals('2024-07-15', $events[1]['date']);

        $this->assertEquals('Joined Organization', $events[2]['title']);
        $this->assertEquals('2024-01-15', $events[2]['date']);
    }
}
