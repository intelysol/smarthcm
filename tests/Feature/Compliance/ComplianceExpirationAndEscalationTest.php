<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Compliance\Models\HcmComplianceRequirementType;
use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use App\Domains\Compliance\Services\ComplianceAlertEscalationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceExpirationAndEscalationTest extends TestCase
{
    use RefreshDatabase;

    public function test_scans_and_creates_escalations_for_expiring_permits(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ESC-01',
            'employee_number' => 'EMP-ESC-01',
            'first_name' => 'James',
            'last_name' => 'Wilson',
            'official_email' => 'wilson@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        // Work permit expiring in 25 days (within 30-day critical window)
        $permit = HcmEmployeeWorkPermit::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'permit_type' => 'Standard Work Permit',
            'permit_number' => 'WP-EXP-30DAYS',
            'country' => 'USA',
            'issue_date' => now()->subYear()->toDateString(),
            'effective_from' => now()->subYear()->toDateString(),
            'expiry_date' => now()->addDays(25)->toDateString(),
            'status' => 'active',
            'is_current' => true,
        ]);

        $service = app(ComplianceAlertEscalationService::class);
        $escalations = $service->scanAndEscalate((string) $tenant->id);

        $this->assertNotEmpty($escalations);
        $this->assertDatabaseHas('hcm_compliance_escalations', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'tier' => '30_days',
            'recipient_role' => 'manager',
        ]);
    }
}
