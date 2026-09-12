<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Compliance\Models\HcmComplianceRequirementType;
use App\Domains\Compliance\Services\ComplianceBulkService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceBulkOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_assign_and_preview_validation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'BULK-01',
            'employee_number' => 'BULK-01',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'johndoe@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'BULK-02',
            'employee_number' => 'BULK-02',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'official_email' => 'janesmith@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $type = HcmComplianceRequirementType::create([
            'tenant_id' => $tenant->id,
            'code' => 'CYBER',
            'name' => 'Cybersecurity',
            'is_active' => true,
        ]);

        $req = HcmComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'requirement_type_id' => $type->id,
            'code' => 'CYBER_SEC_2026',
            'name' => 'Cybersecurity Awareness 2026',
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        $service = app(ComplianceBulkService::class);

        // 1. Bulk assign
        $results = $service->bulkAssign(
            (string) $tenant->id,
            (string) $req->id,
            [(string) $emp1->id, (string) $emp2->id],
            $user
        );

        $this->assertEquals(2, $results['assigned_count']);
        $this->assertDatabaseHas('hcm_employee_compliance_requirements', [
            'tenant_id' => $tenant->id,
            'employee_id' => $emp1->id,
            'requirement_id' => $req->id,
        ]);
        $this->assertDatabaseHas('hcm_employee_compliance_requirements', [
            'tenant_id' => $tenant->id,
            'employee_id' => $emp2->id,
            'requirement_id' => $req->id,
        ]);

        // 2. Bulk preview validation via bulkUpload in dry run mode
        $preview = $service->bulkUpload((string) $tenant->id, 'work_permit', [
            [
                'employee_code' => 'BULK-01',
                'permit_number' => 'WP-BULK-1',
                'country' => 'USA',
                'expiry_date' => now()->addYear()->toDateString(),
            ],
            [
                'employee_code' => 'NON_EXISTENT_EMP',
                'permit_number' => 'WP-BULK-2',
                'country' => 'USA',
                'expiry_date' => now()->addYear()->toDateString(),
            ],
        ], $user, true);

        $this->assertEquals(2, $preview['total_rows']);
        $this->assertEquals(1, $preview['valid_count']);
        $this->assertEquals(1, $preview['error_count']);
    }
}
