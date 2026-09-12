<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionSecurityService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelActionSecurityAndCompensationProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_isolation_and_compensation_masking(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $companyA = Company::factory()->create(['tenant_id' => $tenantA->id]);

        $empA = Employee::create([
            'tenant_id' => $tenantA->id,
            'company_id' => $companyA->id,
            'employee_code' => 'EMP-SEC-1',
            'employee_number' => 'EMP-SEC-1',
            'first_name' => 'Oscar',
            'last_name' => 'Martinez',
            'official_email' => 'oscar@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenantA->id,
            'code' => 'COMPENSATION_CHANGE',
            'name' => 'Salary Revision',
        ]);

        $request = PersonnelActionRequest::create([
            'tenant_id' => $tenantA->id,
            'employee_id' => $empA->id,
            'action_type_id' => $actionType->id,
            'request_number' => 'PA-SEC-01',
            'status' => 'draft',
            'requested_by' => $userA->id,
            'requested_at' => now(),
            'effective_date' => now()->toDateString(),
        ]);

        $securityService = new PersonnelActionSecurityService();

        // 1. Cross-tenant isolation check
        try {
            $securityService->authorizeRequestAccess($userB, $request);
            $this->fail('Expected AuthorizationException for cross-tenant access');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Cross-tenant personnel action access prohibited', $e->getMessage());
        }

        // 2. Compensation data masking for unauthorized viewers
        $regularUserWithoutComp = User::factory()->create(['tenant_id' => $tenantA->id, 'is_platform_admin' => false]);
        $maskedChanges = $securityService->maskCompensationIfUnauthorized($regularUserWithoutComp, [
            ['field_name' => 'base_salary', 'old_value' => '150000', 'new_value' => '180000'],
            ['field_name' => 'department_id', 'old_value' => 'D1', 'new_value' => 'D2'],
        ]);

        $this->assertEquals('*** CONFIDENTIAL ***', $maskedChanges[0]['old_value']);
        $this->assertEquals('*** CONFIDENTIAL ***', $maskedChanges[0]['new_value']);
        $this->assertEquals('D1', $maskedChanges[1]['old_value']);
    }
}
