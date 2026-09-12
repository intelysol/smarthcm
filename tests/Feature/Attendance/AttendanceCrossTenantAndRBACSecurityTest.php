<?php

namespace Tests\Feature\Attendance;

use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Services\AttendanceAuthorizationService;
use App\Domains\Attendance\Services\AttendanceDeviceService;
use App\Domains\Attendance\Services\AttendanceProcessor;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendanceDefaultDataSeeder;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCrossTenantAndRBACSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_tenant_isolation_and_cross_tenant_access_rejection(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $companyA = Company::factory()->create(['tenant_id' => $tenantA->id]);
        $companyB = Company::factory()->create(['tenant_id' => $tenantB->id]);

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

        $this->grant($userA, ['hcm.attendance.view', 'hcm.attendance.manage']);
        $this->grant($userB, ['hcm.attendance.view', 'hcm.attendance.manage']);

        $empA = $this->createEmployee($tenantA->id, $companyA->id, ['employee_number' => 'EMP-A']);
        $empB = $this->createEmployee($tenantB->id, $companyB->id, ['employee_number' => 'EMP-B']);

        (new AttendanceDefaultDataSeeder())->seedTenantData($tenantA->id);
        (new AttendanceDefaultDataSeeder())->seedTenantData($tenantB->id);

        $deviceService = app(AttendanceDeviceService::class);
        $processor = app(AttendanceProcessor::class);
        $authService = app(AttendanceAuthorizationService::class);

        // Ingest Tenant A event
        $deviceService->ingestRawEvents($tenantA->id, null, [
            ['employee_identifier' => 'EMP-A', 'timestamp' => '2026-09-07 08:00:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-A', 'timestamp' => '2026-09-07 17:00:00', 'event_type' => 'OUT'],
        ]);

        $sessionA = $processor->processEmployeeDate($empA, '2026-09-07');

        // User A can view Session A
        $this->assertTrue($authService->canViewSession($userA, $sessionA));

        // User B (Tenant B) cannot view Session A
        $this->assertFalse($authService->canViewSession($userB, $sessionA));
    }

    public function test_employee_self_service_and_manager_scoping(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $userEmp = User::factory()->create(['tenant_id' => $tenant->id]);
        $userMgr = User::factory()->create(['tenant_id' => $tenant->id]);
        $userPeer = User::factory()->create(['tenant_id' => $tenant->id]);

        $manager = $this->createEmployee($tenant->id, $company->id, [
            'user_id' => $userMgr->id,
        ]);

        $employee = $this->createEmployee($tenant->id, $company->id, [
            'user_id' => $userEmp->id,
            'reporting_manager_id' => $manager->id,
            'employee_number' => 'EMP-REP',
        ]);

        $peer = $this->createEmployee($tenant->id, $company->id, [
            'user_id' => $userPeer->id,
        ]);

        (new AttendanceDefaultDataSeeder())->seedTenantData($tenant->id);

        $deviceService = app(AttendanceDeviceService::class);
        $processor = app(AttendanceProcessor::class);
        $authService = app(AttendanceAuthorizationService::class);

        $deviceService->ingestRawEvents($tenant->id, null, [
            ['employee_identifier' => 'EMP-REP', 'timestamp' => '2026-09-07 08:00:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-REP', 'timestamp' => '2026-09-07 17:00:00', 'event_type' => 'OUT'],
        ]);

        $session = $processor->processEmployeeDate($employee, '2026-09-07');

        // Employee can view own session
        $this->assertTrue($authService->canViewSession($userEmp, $session));

        // Direct Manager can view employee session
        $this->assertTrue($authService->canViewSession($userMgr, $session));

        // Unrelated peer cannot view session (no HR permission)
        $this->assertFalse($authService->canViewSession($userPeer, $session));
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'john.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'att_test'], ['label' => 'Attendance Test']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
