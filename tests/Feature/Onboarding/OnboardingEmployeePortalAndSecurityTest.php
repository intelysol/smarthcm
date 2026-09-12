<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingCaseTask;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Onboarding\Services\OnboardingSecurityService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingEmployeePortalAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_portal_scoping_sensitive_masking_and_isolation(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employeeA = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-SEC-01',
            'employee_number' => 'EMP-SEC-01',
            'first_name' => 'Alice',
            'last_name' => 'Anderson',
            'official_email' => 'alice@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $userA = User::factory()->create(['tenant_id' => $tenant->id]);
        $employeeA->update(['user_id' => $userA->id]);

        $employeeB = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-SEC-02',
            'employee_number' => 'EMP-SEC-02',
            'first_name' => 'Bob',
            'last_name' => 'Baker',
            'official_email' => 'bob@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $userB = User::factory()->create(['tenant_id' => $tenant->id]);
        $employeeB->update(['user_id' => $userB->id]);

        $template = HcmOnboardingTemplate::create(['tenant_id' => $tenant->id, 'name' => 'T', 'code' => 'T-SEC']);
        $version = HcmOnboardingTemplateVersion::create(['tenant_id' => $tenant->id, 'template_id' => $template->id, 'version_number' => 1]);

        $caseA = HcmOnboardingCase::create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ONB-SEC-A',
            'employee_id' => $employeeA->id,
            'template_version_id' => $version->id,
            'start_date' => now()->toDateString(),
        ]);

        $taskA = HcmOnboardingCaseTask::create([
            'tenant_id' => $tenant->id,
            'case_id' => $caseA->id,
            'title' => 'Sign Code of Conduct',
            'owner_role' => 'employee',
        ]);

        // 1. Employee A accesses own onboarding case via portal API
        $response = $this->actingAs($userA)->getJson('/api/v1/me/onboarding');
        $response->assertStatus(200);
        $response->assertJsonFragment(['case_number' => 'ONB-SEC-A']);

        // 2. Employee B attempts to complete Employee A's task -> 403 Forbidden
        $forbiddenResponse = $this->actingAs($userB)->postJson("/api/v1/me/onboarding/tasks/{$taskA->id}/complete");
        $forbiddenResponse->assertStatus(403);

        // 3. Security Service: Sensitive banking data masking
        $security = new OnboardingSecurityService();
        $masked = $security->maskSensitiveBankingData([
            'bank_account_number' => '9876543210',
            'tax_identification_number' => '123456789',
            'bank_name' => 'Global Chase Bank',
        ]);

        $this->assertEquals('******3210', $masked['bank_account_number']);
        $this->assertEquals('*****6789', $masked['tax_identification_number']);
        $this->assertEquals('Global Chase Bank', $masked['bank_name']);

        // 4. Cross-tenant isolation check
        $userOtherTenant = User::factory()->create(['tenant_id' => $otherTenant->id]);
        try {
            $security->authorizeCaseAccess($userOtherTenant, $caseA);
            $this->fail('Expected AuthorizationException for cross-tenant case access');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Cross-tenant onboarding case access prohibited', $e->getMessage());
        }
    }
}
