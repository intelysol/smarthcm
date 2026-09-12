<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\HcmEmployeeLicense;
use App\Domains\Compliance\Services\ComplianceVerificationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceVerificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_source_verification_workflow(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-VER-01',
            'employee_number' => 'EMP-VER-01',
            'first_name' => 'Lisa',
            'last_name' => 'Cuddy',
            'official_email' => 'cuddy@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $license = HcmEmployeeLicense::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'license_type' => 'Medical License',
            'license_name' => 'State Medical Director License',
            'license_number' => 'MD-DIR-112',
            'issuing_authority' => 'State Board',
            'country' => 'USA',
            'state_province' => 'NJ',
            'issue_date' => now()->subYear()->toDateString(),
            'effective_from' => now()->subYear()->toDateString(),
            'status' => 'pending_verification',
        ]);

        $service = app(ComplianceVerificationService::class);

        $verification = $service->recordVerification(
            (string) $employee->id,
            'license',
            (string) $license->id,
            'document_verification',
            'verified',
            'REG-REF-77261',
            'Primary state board registry check passed',
            $user
        );

        $this->assertDatabaseHas('hcm_compliance_verifications', [
            'id' => $verification->id,
            'verifiable_id' => $license->id,
            'status' => 'verified',
            'verification_source' => 'document_verification',
        ]);

        // License status should now be updated to active
        $license->refresh();
        $this->assertEquals('verified', $license->verification_status);
    }
}
