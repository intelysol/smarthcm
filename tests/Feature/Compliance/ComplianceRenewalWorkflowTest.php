<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use App\Domains\Compliance\Services\ComplianceRenewalService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceRenewalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_renewal_initiation_and_completion_lifecycle(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REN-01',
            'employee_number' => 'EMP-REN-01',
            'first_name' => 'Robert',
            'last_name' => 'Chase',
            'official_email' => 'chase@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $permit = HcmEmployeeWorkPermit::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'permit_number' => 'WP-EXP-1234',
            'country' => 'GBR',
            'permit_type' => 'Tier 2 General',
            'issue_date' => now()->subYears(2)->toDateString(),
            'effective_from' => now()->subYears(2)->toDateString(),
            'expiry_date' => now()->addDays(20)->toDateString(),
            'status' => 'active',
        ]);

        $service = app(ComplianceRenewalService::class);

        // 1. Initiate renewal
        $renewal = $service->initiateRenewal(
            (string) $employee->id,
            'work_permit',
            (string) $permit->id,
            $user
        );

        $this->assertDatabaseHas('hcm_compliance_renewals', [
            'id' => $renewal->id,
            'renewable_id' => $permit->id,
            'status' => 'initiated',
        ]);

        // 2. Complete renewal with new expiration date
        $newExpiration = now()->addYears(3)->toDateString();
        $service->completeRenewal(
            (string) $renewal->id,
            $newExpiration,
            $user
        );

        $renewal->refresh();
        $permit->refresh();

        $this->assertEquals('completed', $renewal->status);
        $this->assertEquals($newExpiration, $permit->expiry_date->toDateString());
    }
}
