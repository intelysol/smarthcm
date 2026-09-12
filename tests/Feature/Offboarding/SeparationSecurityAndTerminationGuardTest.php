<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationSecurityService;
use App\Domains\Offboarding\Services\SeparationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SeparationSecurityAndTerminationGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_termination_guardrails_tenant_isolation_and_er_masking(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $companyA = Company::factory()->create(['tenant_id' => $tenantA->id]);

        $employeeA = Employee::create([
            'tenant_id' => $tenantA->id,
            'company_id' => $companyA->id,
            'employee_code' => 'EMP-SEC-29',
            'employee_number' => 'EMP-SEC-29',
            'first_name' => 'Creed',
            'last_name' => 'Bratton',
            'official_email' => 'creed@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $userWithoutPerm = User::factory()->create(['tenant_id' => $tenantA->id, 'is_platform_admin' => false]);
        $userTenantB = User::factory()->create(['tenant_id' => $tenantB->id]);

        $termType = SeparationType::create([
            'tenant_id' => $tenantA->id,
            'code' => 'INVOLUNTARY_TERMINATION',
            'name' => 'Involuntary Termination',
            'category' => 'involuntary',
        ]);

        $service = new SeparationService();

        // 1. Guardrail: User without 'separations.terminate' cannot initiate involuntary termination
        try {
            $service->createRequest($userWithoutPerm, [
                'employee_id' => $employeeA->id,
                'separation_type_id' => $termType->id,
                'proposed_last_working_day' => now()->toDateString(),
                'reason' => 'Misconduct',
            ]);
            $this->fail('Expected ValidationException for unauthorized involuntary termination');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('separation_type', $e->errors());
            $this->assertStringContainsString('separations.terminate', $e->errors()['separation_type'][0]);
        }

        // 2. Tenant Isolation
        $adminA = User::factory()->create(['tenant_id' => $tenantA->id, 'is_platform_admin' => true]);
        $requestA = $service->createRequest($adminA, [
            'employee_id' => $employeeA->id,
            'separation_type_id' => $termType->id,
            'proposed_last_working_day' => now()->toDateString(),
            'reason' => 'Authorized termination',
            'er_case_reference_id' => 'ER-CONFIDENTIAL-999',
        ]);

        $securityService = new SeparationSecurityService();
        try {
            $securityService->authorizeRequestAccess($userTenantB, $requestA);
            $this->fail('Expected AuthorizationException for cross-tenant access');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Cross-tenant offboarding access prohibited', $e->getMessage());
        }

        // 3. Mask confidential ER case details for regular users
        $maskedEr = $securityService->maskErCaseIfUnauthorized($userWithoutPerm, $requestA->er_case_reference_id);
        $this->assertEquals('REF-CONFIDENTIAL', $maskedEr);
    }
}
