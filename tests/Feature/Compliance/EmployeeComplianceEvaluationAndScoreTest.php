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

class EmployeeComplianceEvaluationAndScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_computes_compliance_score_and_records_snapshot(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-SCORE-01',
            'employee_number' => 'EMP-SCORE-01',
            'first_name' => 'Eric',
            'last_name' => 'Foreman',
            'official_email' => 'foreman@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $type = HcmComplianceRequirementType::create([
            'tenant_id' => $tenant->id,
            'code' => 'CERT',
            'name' => 'Certification',
            'is_active' => true,
        ]);

        // Req 1: Satisfied
        $req1 = HcmComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'requirement_type_id' => $type->id,
            'code' => 'CERT_BLS',
            'name' => 'Basic Life Support',
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        HcmEmployeeComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'requirement_id' => $req1->id,
            'status' => 'compliant',
            'fulfilled_at' => now(),
        ]);

        // Req 2: Pending (Mandatory)
        $req2 = HcmComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'requirement_type_id' => $type->id,
            'code' => 'CERT_ACLS',
            'name' => 'Advanced Cardiac Life Support',
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        HcmEmployeeComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'requirement_id' => $req2->id,
            'status' => 'required',
        ]);

        $service = app(ComplianceEvaluationService::class);
        $snapshot = $service->evaluateEmployee((string) $employee->id);

        // 1 of 2 satisfied = 50%
        $this->assertEquals(50.0, (float) $snapshot->compliance_score);
        $this->assertEquals('non_compliant', $snapshot->overall_status);

        $this->assertDatabaseHas('hcm_employee_compliance_snapshots', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'compliance_score' => 50.0,
            'overall_status' => 'non_compliant',
        ]);
    }
}
