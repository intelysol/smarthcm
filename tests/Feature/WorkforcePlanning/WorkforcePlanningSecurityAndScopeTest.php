<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanningSecurityService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforcePlanningSecurityAndScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_tenant_access_is_prohibited(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $userTenant1 = User::factory()->create(['tenant_id' => $tenant1->id]);

        $planService = app(WorkforcePlanService::class);
        $securityService = app(WorkforcePlanningSecurityService::class);

        $planTenant2 = $planService->createPlan([
            'tenant_id' => $tenant2->id,
            'code' => 'WFP-T2',
            'name' => 'Tenant 2 Plan',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        $this->expectException(AuthorizationException::class);
        $securityService->authorizePlanAccess($userTenant1, $planTenant2);
    }

    public function test_manager_department_scope_restriction(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = \App\Domains\Organization\Models\BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $deptA = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);
        $deptB = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Marketing', 'department_code' => 'MKT']);

        $empDeptA = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $deptA->id,
            'employee_code' => 'EMP-ENG-01',
            'employee_number' => 'EMP-ENG-01',
            'first_name' => 'Eng',
            'last_name' => 'Manager',
            'official_email' => 'eng.mgr@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $managerUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $empDeptA->update(['user_id' => $managerUser->id]);

        $planService = app(WorkforcePlanService::class);
        $securityService = app(WorkforcePlanningSecurityService::class);

        // Plan scoped to Department B (Marketing)
        $marketingPlan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-MKT',
            'name' => 'Marketing Plan',
            'planning_cycle' => 'FY2027',
            'department_id' => $deptB->id,
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        $this->expectException(AuthorizationException::class);
        $securityService->authorizePlanAccess($managerUser, $marketingPlan);
    }
}
