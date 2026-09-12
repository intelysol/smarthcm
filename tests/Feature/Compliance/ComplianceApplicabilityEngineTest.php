<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Compliance\Models\HcmComplianceRequirementType;
use App\Domains\Compliance\Services\ComplianceApplicabilityService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceApplicabilityEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluates_applicable_requirements_for_employee(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = \App\Domains\Organization\Models\BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Technology',
            'code' => 'BU-TECH',
            'status' => 'active',
        ]);

        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-ENG',
            'department_name' => 'Engineering',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-COMP-01',
            'employee_number' => 'EMP-COMP-01',
            'first_name' => 'Sara',
            'last_name' => 'Connor',
            'official_email' => 'sara@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $type = HcmComplianceRequirementType::create([
            'tenant_id' => $tenant->id,
            'code' => 'GENERAL',
            'name' => 'General Obligation',
            'is_active' => true,
        ]);

        // 1. General requirement (applies to all)
        $req1 = HcmComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'requirement_type_id' => $type->id,
            'code' => 'CODE_OF_CONDUCT',
            'name' => 'Global Code of Conduct',
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        // 2. Department-specific requirement (matches employee's department)
        $req2 = HcmComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'requirement_type_id' => $type->id,
            'code' => 'SAFETY_TRAINING',
            'name' => 'Department Safety Standard',
            'department_id' => $dept->id,
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        // 3. Other company requirement (should NOT match)
        $otherCompany = Company::factory()->create(['tenant_id' => $tenant->id]);
        $req3 = HcmComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'requirement_type_id' => $type->id,
            'code' => 'OTHER_CO_REQ',
            'name' => 'Other Entity Requirement',
            'legal_entity_id' => $otherCompany->id,
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        $service = app(ComplianceApplicabilityService::class);
        $applicable = $service->getApplicableRequirements($employee);

        $applicableCodes = $applicable->pluck('code')->all();

        $this->assertContains('CODE_OF_CONDUCT', $applicableCodes);
        $this->assertContains('SAFETY_TRAINING', $applicableCodes);
        $this->assertNotContains('OTHER_CO_REQ', $applicableCodes);
    }
}
