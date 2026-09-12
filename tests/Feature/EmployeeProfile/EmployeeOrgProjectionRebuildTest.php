<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Jobs\RebuildEmployeeOrgReadModelJob;
use App\Domains\EmployeeProfile\Models\EmployeeOrgReadModel;
use App\Domains\EmployeeProfile\Services\EmployeeOrgProjectionService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeOrgProjectionRebuildTest extends TestCase
{
    use RefreshDatabase;

    public function test_projection_derivation_and_job_execution(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'Main BU', 'code' => 'BU-01']);

        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Finance', 'department_code' => 'FIN']);
        $desig = Designation::create(['tenant_id' => $tenant->id, 'designation_code' => 'CFO', 'designation_name' => 'CFO']);

        $cfo = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'employee_code' => 'FIN-001',
            'employee_number' => 'FIN-001',
            'first_name' => 'Bruce',
            'last_name' => 'Wayne',
            'official_email' => 'bruce@wayne.com',
            'employment_status' => 'active',
            'joining_date' => now()->subYears(5)->toDateString(),
        ]);

        $service = new EmployeeOrgProjectionService();

        // 1. Project single employee
        $projection = $service->projectEmployee($cfo);
        $this->assertInstanceOf(EmployeeOrgReadModel::class, $projection);
        $this->assertEquals('Bruce Wayne', $projection->full_name);
        $this->assertEquals('Finance', $projection->department_name);
        $this->assertEquals('CFO', $projection->job_title);
        $this->assertStringContainsString('bruce', $projection->searchable_text);

        // 2. Run rebuild job
        $job = new RebuildEmployeeOrgReadModelJob($tenant->id);
        $results = $job->handle($service);
        $this->assertEquals(1, $results[$tenant->id]);
        $this->assertTrue(EmployeeOrgReadModel::where('employee_id', $cfo->id)->exists());
    }
}
