<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\HcmComplianceRequirementType;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_rest_api_lifecycle(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-API-COMP',
            'employee_number' => 'EMP-API-COMP',
            'first_name' => 'Remy',
            'last_name' => 'Hadley',
            'official_email' => 'hadley@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $type = HcmComplianceRequirementType::create([
            'tenant_id' => $tenant->id,
            'code' => 'MED',
            'name' => 'Medical Clearance',
            'is_active' => true,
        ]);

        // 1. Requirement Types API
        $resTypes = $this->actingAs($user)->getJson('/api/v1/hcm/compliance/requirement-types');
        $resTypes->assertOk();
        $resTypes->assertJsonPath('success', true);

        // 2. Create Requirement via API
        $resReq = $this->actingAs($user)->postJson('/api/v1/hcm/compliance/requirements', [
            'requirement_type_id' => $type->id,
            'code' => 'IMMUNIZATION_RECORD',
            'name' => 'Standard Immunization Record',
            'is_mandatory' => true,
        ]);
        $resReq->assertCreated();
        $reqId = $resReq->json('data.id');

        // 3. Assign to employee via API
        $resAssign = $this->actingAs($user)->postJson("/api/v1/hcm/compliance/employees/{$employee->id}/compliance", [
            'compliance_requirement_id' => $reqId,
            'due_date' => now()->addDays(30)->toDateString(),
        ]);
        $resAssign->assertCreated();

        // 4. Create Work Permit via API
        $resPermit = $this->actingAs($user)->postJson("/api/v1/hcm/compliance/work-permits/{$employee->id}", [
            'permit_number' => 'WP-USA-554433',
            'country' => 'USA',
            'issue_date' => now()->subMonths(2)->toDateString(),
            'expiry_date' => now()->addMonths(10)->toDateString(),
        ]);
        $resPermit->assertCreated();

        // 5. Create Visa Record via API
        $resVisa = $this->actingAs($user)->postJson("/api/v1/hcm/compliance/visas/{$employee->id}", [
            'visa_type' => 'O-1',
            'visa_number' => 'V-998844',
            'issuing_country' => 'USA',
            'issue_date' => now()->subMonths(2)->toDateString(),
            'expiry_date' => now()->addMonths(22)->toDateString(),
        ]);
        $resVisa->assertCreated();

        // 6. Create License via API
        $resLic = $this->actingAs($user)->postJson("/api/v1/hcm/compliance/licenses", [
            'employee_id' => $employee->id,
            'license_name' => 'State Medical License',
            'license_number' => 'MED-12345',
            'licensing_board' => 'Board of Medical Examiners',
            'issuing_jurisdiction' => 'NJ',
            'issuing_country' => 'USA',
            'issue_date' => now()->subYear()->toDateString(),
            'expiration_date' => now()->addYear()->toDateString(),
        ]);
        $resLic->assertCreated();

        // 7. Verification API
        $licId = $resLic->json('data.id');
        $resVer = $this->actingAs($user)->postJson('/api/v1/hcm/compliance/verifications', [
            'employee_id' => $employee->id,
            'verifiable_type' => 'license',
            'verifiable_id' => $licId,
            'source' => 'document_verification',
            'status' => 'verified',
        ]);
        $resVer->assertCreated();

        // 8. Controlled Exemption API
        $resExm = $this->actingAs($user)->postJson('/api/v1/hcm/compliance/exemptions', [
            'employee_id' => $employee->id,
            'requirement_id' => $reqId,
            'reason' => 'Temporary Waiver',
            'effective_from' => now()->toDateString(),
            'expiry_date' => now()->addMonths(1)->toDateString(),
        ]);
        $resExm->assertCreated();
        $exmId = $resExm->json('data.id');

        $resApprove = $this->actingAs($user)->postJson("/api/v1/hcm/compliance/exemptions/{$exmId}/approve");
        $resApprove->assertOk();

        // 9. Dashboard Stats API
        $resStats = $this->actingAs($user)->getJson('/api/v1/hcm/compliance/dashboard/stats');
        $resStats->assertOk();
        $resStats->assertJsonPath('success', true);

        // 10. AI Advisory API
        $resAi = $this->actingAs($user)->getJson("/api/v1/hcm/compliance/ai/explain/{$employee->id}");
        $resAi->assertOk();
        $resAi->assertJsonPath('success', true);
        $this->assertNotEmpty($resAi->json('data.summary'));
    }
}
