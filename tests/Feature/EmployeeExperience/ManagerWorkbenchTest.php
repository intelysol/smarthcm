<?php

namespace Tests\Feature\EmployeeExperience;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManagerWorkbenchTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Employee $manager;
    protected Employee $directReport1;
    protected Employee $directReport2;
    protected Employee $unrelatedEmployee;
    protected string $companyId;
    protected string $leaveTypeId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Apex Systems International',
            'slug' => 'apex-systems',
            'tenant_code' => 'APX-ENT-01',
            'status' => 'active',
        ]);

        $this->companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $this->companyId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Apex Corp',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Manager Employee
        $this->manager = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->companyId,
            'employee_number' => 'MGR-001',
            'employee_code' => 'MGR-001',
            'first_name' => 'Tariq',
            'last_name' => 'Mahmood',
            'official_email' => 'tariq.mahmood@apexsystems.com',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYears(3)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Direct Report 1
        $this->directReport1 = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->companyId,
            'employee_number' => 'EMP-101',
            'employee_code' => 'EMP-101',
            'first_name' => 'Ali',
            'last_name' => 'Raza',
            'reporting_manager_id' => $this->manager->id,
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYear()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Direct Report 2
        $this->directReport2 = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->companyId,
            'employee_number' => 'EMP-102',
            'employee_code' => 'EMP-102',
            'first_name' => 'Sara',
            'last_name' => 'Iqbal',
            'reporting_manager_id' => $this->manager->id,
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(8)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Unrelated Employee (reports to someone else)
        $this->unrelatedEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->companyId,
            'employee_number' => 'EMP-999',
            'employee_code' => 'EMP-999',
            'first_name' => 'Kamran',
            'last_name' => 'Akmal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(4)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->leaveTypeId = (string) Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $this->leaveTypeId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Casual Leave',
            'code' => 'CASUAL',
            'is_paid' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_manager_dashboard_and_team_attendance_summary(): void
    {
        $today = Carbon::today()->toDateString();

        // Record attendance for Report 1
        DB::table('attendance_sessions')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->directReport1->id,
            'session_date' => $today,
            'actual_start_time' => Carbon::now()->subHours(2),
            'status' => 'present',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->manager->id,
        ])->getJson('/api/manager/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('team_summary.total_members', 2)
            ->assertJsonPath('team_summary.present_today', 1)
            ->assertJsonPath('team_summary.absent_today', 1);
    }

    public function test_manager_team_roster_scoped_to_direct_reports(): void
    {
        $response = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->manager->id,
        ])->getJson('/api/manager/team');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'team');

        $teamNames = collect($response->json('team'))->pluck('name')->toArray();
        $this->assertContains('Ali Raza', $teamNames);
        $this->assertContains('Sara Iqbal', $teamNames);
        $this->assertNotContains('Kamran Akmal', $teamNames); // Unrelated employee excluded
    }

    public function test_manager_approvals_inbox_and_action_routing(): void
    {
        // 1. Seed a leave application for directReport1
        $leaveId = (string) Str::uuid();
        DB::table('leave_applications')->insert([
            'id' => $leaveId,
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->directReport1->id,
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => Carbon::now()->addDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(3)->toDateString(),
            'duration' => 2.0,
            'reason' => 'Doctor appointment',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Query Manager Approvals Inbox
        $inboxResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->manager->id,
        ])->getJson('/api/manager/approvals');

        $inboxResponse->assertStatus(200)
            ->assertJsonCount(1, 'approvals')
            ->assertJsonPath('approvals.0.id', $leaveId)
            ->assertJsonPath('approvals.0.employee.name', 'Ali Raza');

        // 3. Manager Approves Leave Request
        $actionResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->manager->id,
        ])->postJson("/api/manager/approvals/LEAVE/{$leaveId}/action", [
            'action' => 'approve',
            'comments' => 'Approved, enjoy your break.',
        ]);

        $actionResponse->assertStatus(200)
            ->assertJsonPath('status', 'APPROVED');

        // 4. Verify leave application record in DB is now approved
        $leave = DB::table('leave_applications')->where('id', $leaveId)->first();
        $this->assertEquals('approved', $leave->status);

        // 5. Verify audit record was created
        $audit = DB::table('hcm_experience_audits')
            ->where('tenant_id', $this->tenant->id)
            ->where('action_type', 'APPROVAL_DECISION')
            ->where('target_id', $leaveId)
            ->first();
        $this->assertNotNull($audit);
    }

    public function test_manager_cannot_approve_out_of_scope_request(): void
    {
        // Seed leave for unrelated employee
        $leaveId = (string) Str::uuid();
        DB::table('leave_applications')->insert([
            'id' => $leaveId,
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->unrelatedEmployee->id,
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => Carbon::now()->addDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(3)->toDateString(),
            'duration' => 2.0,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attempt approval by Tariq Mahmood (who is NOT Kamran Akmal's manager)
        $actionResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->manager->id,
        ])->postJson("/api/manager/approvals/LEAVE/{$leaveId}/action", [
            'action' => 'approve',
        ]);

        $actionResponse->assertStatus(403);
    }

    public function test_manager_alerts_and_capacity_metrics(): void
    {
        $alertsResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->manager->id,
        ])->getJson('/api/manager/alerts');

        $alertsResponse->assertStatus(200)
            ->assertJsonStructure(['alerts']);

        $capResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->manager->id,
        ])->getJson('/api/manager/capacity');

        $capResponse->assertStatus(200)
            ->assertJsonPath('capacity.planned_headcount', 2);
        $this->assertEquals(16.0, (float) $capResponse->json('capacity.scheduled_hours_today'));
    }

    public function test_web_portal_and_manager_workbench_rendering(): void
    {
        // 1. Employee Portal Home
        $portalResponse = $this->get("/portal?tenant_id={$this->tenant->id}&employee_id={$this->directReport1->id}");
        $portalResponse->assertStatus(200)
            ->assertSee('Flow HCM', false)
            ->assertSee('Digital Workplace', false)
            ->assertSee('My Tasks', false);

        // 2. Manager Workbench Home
        $mgrResponse = $this->get("/portal/manager/workbench?tenant_id={$this->tenant->id}&manager_id={$this->manager->id}");
        $mgrResponse->assertStatus(200)
            ->assertSee('Manager Daily Workbench', false)
            ->assertSee('Direct Reports Roster', false)
            ->assertSee('Ali Raza', false);

        // 3. Manager Approval Inbox View
        $inboxResponse = $this->get("/portal/manager/approvals?tenant_id={$this->tenant->id}&manager_id={$this->manager->id}");
        $inboxResponse->assertStatus(200)
            ->assertSee('Manager Approval Inbox', false);
    }
}
