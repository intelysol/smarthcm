<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Compliance\Models\HcmComplianceRequirementType;
use App\Domains\Compliance\Services\ComplianceExemptionService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceExemptionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_controlled_exemption_request_and_approval(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-EXM-01',
            'employee_number' => 'EMP-EXM-01',
            'first_name' => 'Allison',
            'last_name' => 'Cameron',
            'official_email' => 'cameron@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $type = HcmComplianceRequirementType::create([
            'tenant_id' => $tenant->id,
            'code' => 'TRAINING',
            'name' => 'Training Program',
            'is_active' => true,
        ]);

        $req = HcmComplianceRequirement::create([
            'tenant_id' => $tenant->id,
            'requirement_type_id' => $type->id,
            'code' => 'ANNUAL_ETHICS',
            'name' => 'Annual Corporate Ethics Seminar',
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        $service = app(ComplianceExemptionService::class);

        // 1. Request exemption
        $exemption = $service->requestExemption(
            (string) $employee->id,
            (string) $req->id,
            'Maternity Leave Extension',
            now()->toDateString(),
            now()->addMonths(6)->toDateString(),
            null,
            $user
        );

        $this->assertEquals('requested', $exemption->status);

        // 2. Approve exemption
        $approved = $service->approveExemption(
            (string) $exemption->id,
            $approver
        );

        $this->assertEquals('approved', $approved->status);
        $this->assertEquals($approver->id, $approved->approved_by);
    }
}
