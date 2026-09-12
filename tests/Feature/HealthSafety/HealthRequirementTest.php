<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmHealthRequirement;
use App\Domains\HealthSafety\Models\HcmHealthRequirementType;
use App\Domains\HealthSafety\Services\OccupationalHealthRequirementService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthRequirementTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_requirements_creation_and_automatic_applicability(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Manufacturing BU',
            'code' => 'BU-MFG',
            'status' => 'active',
        ]);

        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-MFG',
            'department_name' => 'Operations',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-HLT-01',
            'employee_number' => 'EMP-HLT-01',
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
            'official_email' => 'alex.rivera@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $type = HcmHealthRequirementType::create([
            'tenant_id' => $tenant->id,
            'code' => 'audiometric_surveillance',
            'name' => 'Audiometric Testing',
            'is_active' => true,
        ]);

        $req = HcmHealthRequirement::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'health_requirement_type_id' => $type->id,
            'code' => 'AUD-01',
            'name' => 'Annual Hearing Test',
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        $service = app(OccupationalHealthRequirementService::class);
        $assigned = $service->assignApplicableRequirements($employee);

        $this->assertCount(1, $assigned);
        $this->assertEquals('required', $assigned->first()->status);
        $this->assertEquals($req->id, $assigned->first()->health_requirement_id);
    }
}

