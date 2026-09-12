<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Compliance\Models\HcmComplianceRequirementType;
use App\Domains\Compliance\Models\HcmEmployeeComplianceRequirement;
use App\Domains\Compliance\Services\ComplianceEvaluationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompliancePersonnelActionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluates_readiness_for_personnel_actions_and_detects_blockers(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ACT-01',
            'employee_number' => 'EMP-ACT-01',
            'first_name' => 'Chris',
            'last_name' => 'Taub',
            'official_email' => 'taub@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $type = HcmComplianceRequirementType::create([
            'tenant_id' => $tenant->id,
            'code' => 'LICENSE',
            'name' => 'Professional License',
            'is_active' => true,
        ]);

        // Mandatory requirement that is breached
        $req = HcmComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'requirement_type_id' => $type->id,
            'code' => 'SURGICAL_CERT',
            'name' => 'Board Certification in Surgery',
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        HcmEmployeeComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'requirement_id' => $req->id,
            'status' => 'non_compliant',
        ]);

        $service = app(ComplianceEvaluationService::class);
        $eval = $service->evaluateEmployee((string) $employee->id);

        $this->assertEquals('non_compliant', $eval->overall_status);
        $this->assertGreaterThanOrEqual(1, $eval->total_requirements);
    }
}
