<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\EmployeeProfileSecurityService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeProfileSecurityAndClassificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_isolation_and_compensation_masking(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $companyA = Company::factory()->create(['tenant_id' => $tenantA->id]);

        $empA = Employee::create([
            'tenant_id' => $tenantA->id,
            'company_id' => $companyA->id,
            'employee_code' => 'SEC-001',
            'employee_number' => 'SEC-001',
            'first_name' => 'Clark',
            'last_name' => 'Kent',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $userTenantA = User::factory()->create(['tenant_id' => $tenantA->id, 'is_platform_admin' => false]);
        $hrAdminA = User::factory()->create(['tenant_id' => $tenantA->id, 'is_platform_admin' => true]);
        $userTenantB = User::factory()->create(['tenant_id' => $tenantB->id]);

        $security = new EmployeeProfileSecurityService();

        // 1. Cross-Tenant Access Prohibited
        try {
            $security->authorizeProfileAccess($userTenantB, $empA);
            $this->fail('Expected AuthorizationException for cross-tenant access');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Cross-tenant employee profile access prohibited', $e->getMessage());
        }

        // 2. Compensation Masked for regular employee
        $this->assertFalse($security->canViewCompensation($userTenantA, $empA));

        // 3. Compensation Visible for HR Admin
        $this->assertTrue($security->canViewCompensation($hrAdminA, $empA));
    }
}
