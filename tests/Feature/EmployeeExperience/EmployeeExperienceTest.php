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

class EmployeeExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Employee $employee;
    protected Employee $otherEmployee;
    protected User $user;
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

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Zain Ahmed',
            'email' => 'zain.ahmed@apexsystems.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $this->employee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'company_id' => $this->companyId,
            'employee_number' => 'EMP-001',
            'employee_code' => 'EMP-001',
            'first_name' => 'Zain',
            'last_name' => 'Ahmed',
            'official_email' => 'zain.ahmed@apexsystems.com',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(6)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->otherEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->companyId,
            'employee_number' => 'EMP-002',
            'employee_code' => 'EMP-002',
            'first_name' => 'Fatima',
            'last_name' => 'Khan',
            'official_email' => 'fatima.khan@apexsystems.com',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(12)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->leaveTypeId = (string) Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $this->leaveTypeId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Annual Leave',
            'code' => 'ANNUAL',
            'is_paid' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed leave balance for employee
        DB::table('leave_balances')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveTypeId,
            'year' => (int) Carbon::now()->year,
            'entitled' => 20,
            'earned' => 5,
            'used' => 2,
            'pending' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_employee_dashboard_aggregates_workplace_data(): void
    {
        $response = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->getJson('/api/me/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'employee' => ['id', 'name', 'employee_code', 'department', 'designation'],
                'attendance' => ['status', 'clock_in', 'clock_out', 'worked_hours'],
                'schedule' => ['shift_name', 'start_time', 'end_time', 'location'],
                'leave_summary' => ['entitled_days', 'used_days', 'pending_days', 'remaining_days'],
                'tasks' => ['pending_count', 'items'],
                'requests' => ['pending_count', 'total_count', 'items'],
                'quick_actions',
                'announcements',
            ]);

        $this->assertEquals('Zain Ahmed', $response->json('employee.name'));
        $this->assertEquals(23, $response->json('leave_summary.remaining_days')); // (20 + 5) - 2 = 23
    }

    public function test_employee_tasks_aggregation(): void
    {
        // Setup document hierarchy
        $catId = (string) Str::uuid();
        DB::table('hcm_document_categories')->insert([
            'id' => $catId,
            'tenant_id' => $this->tenant->id,
            'code' => 'POLICY',
            'name' => 'Company Policies',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $typeId = (string) Str::uuid();
        DB::table('hcm_document_types')->insert([
            'id' => $typeId,
            'tenant_id' => $this->tenant->id,
            'category_id' => $catId,
            'code' => 'CODE_OF_CONDUCT',
            'name' => 'Code of Conduct',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $docId = (string) Str::uuid();
        DB::table('documents')->insert([
            'id' => $docId,
            'tenant_id' => $this->tenant->id,
            'title' => 'Code of Conduct 2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empDocId = (string) Str::uuid();
        DB::table('hcm_employee_documents')->insert([
            'id' => $empDocId,
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'document_type_id' => $typeId,
            'document_id' => $docId,
            'title' => 'Code of Conduct 2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reqId = (string) Str::uuid();
        DB::table('employee_document_requirements')->insert([
            'id' => $reqId,
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'document_type_id' => $typeId,
            'employee_document_id' => $empDocId,
            'is_mandatory' => true,
            'status' => 'required',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->getJson('/api/me/tasks');

        $response->assertStatus(200)
            ->assertJsonPath('tasks.0.priority', fn ($p) => in_array($p, ['HIGH', 'URGENT', 'MEDIUM', 'LOW']));

        $tasks = $response->json('tasks');
        $this->assertTrue(collect($tasks)->contains(fn ($t) => str_contains($t['title'], 'Acknowledge Policy') || str_contains($t['title'], 'Profile')));
    }

    public function test_employee_requests_and_leave_application(): void
    {
        // 1. Submit Leave Request
        $applyResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->postJson('/api/me/leave/apply', [
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(7)->toDateString(),
            'duration' => 3.0,
            'reason' => 'Annual family vacation',
        ]);

        $applyResponse->assertStatus(201)
            ->assertJsonPath('status', 'PENDING');

        // 2. Verify leave_balances incremented pending days
        $balance = DB::table('leave_balances')
            ->where('tenant_id', $this->tenant->id)
            ->where('employee_id', $this->employee->id)
            ->first();
        $this->assertEquals(3.0, (float) $balance->pending);

        // 3. Verify request appears in unified requests list
        $requestsResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->getJson('/api/me/requests');

        $requestsResponse->assertStatus(200);
        $this->assertCount(1, $requestsResponse->json('requests'));
        $this->assertEquals('LEAVE', $requestsResponse->json('requests.0.type'));
        $this->assertEquals('PENDING', $requestsResponse->json('requests.0.status'));
        $this->assertEquals('Manager Approval', $requestsResponse->json('requests.0.current_step'));
    }

    public function test_attendance_clock_in_and_out_lifecycle(): void
    {
        // 1. Clock In
        $clockIn = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->postJson('/api/me/attendance/clock', ['action' => 'toggle']);

        $clockIn->assertStatus(200)
            ->assertJsonPath('status', 'CLOCKED_IN');

        // Verify session recorded in DB
        $session = DB::table('attendance_sessions')
            ->where('tenant_id', $this->tenant->id)
            ->where('employee_id', $this->employee->id)
            ->first();
        $this->assertNotNull($session);
        $this->assertNotNull($session->actual_start_time);
        $this->assertNull($session->actual_end_time);

        // 2. Clock Out
        $clockOut = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->postJson('/api/me/attendance/clock', ['action' => 'toggle']);

        $clockOut->assertStatus(200)
            ->assertJsonPath('status', 'CLOCKED_OUT');

        $sessionAfter = DB::table('attendance_sessions')
            ->where('id', $session->id)
            ->first();
        $this->assertNotNull($sessionAfter->actual_end_time);
        $this->assertEquals('present', $sessionAfter->status);
    }

    public function test_secure_payslip_access_and_horizontal_isolation(): void
    {
        $periodId = (string) Str::uuid();
        DB::table('payroll_periods')->insert([
            'id' => $periodId,
            'tenant_id' => $this->tenant->id,
            'period_name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $runId = (string) Str::uuid();
        DB::table('payroll_runs')->insert([
            'id' => $runId,
            'tenant_id' => $this->tenant->id,
            'payroll_period_id' => $periodId,
            'run_number' => 'RUN-2026-09',
            'name' => 'September 2026 Regular Run',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $snapId = (string) Str::uuid();
        DB::table('payroll_calculation_snapshots')->insert([
            'id' => $snapId,
            'tenant_id' => $this->tenant->id,
            'payroll_run_id' => $runId,
            'employee_id' => $this->employee->id,
            'gross_pay' => 5000.00,
            'total_deductions' => 800.00,
            'net_pay' => 4200.00,
            'employee_snapshot' => json_encode(['id' => $this->employee->id]),
            'compensation_snapshot' => json_encode([]),
            'inputs_snapshot' => json_encode([]),
            'earnings_snapshot' => json_encode([]),
            'deductions_snapshot' => json_encode([]),
            'taxes_snapshot' => json_encode([]),
            'employer_contributions_snapshot' => json_encode([]),
            'idempotency_key' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payslipId = (string) Str::uuid();
        DB::table('payroll_payslips')->insert([
            'id' => $payslipId,
            'tenant_id' => $this->tenant->id,
            'payroll_run_id' => $runId,
            'employee_id' => $this->employee->id,
            'payroll_calculation_snapshot_id' => $snapId,
            'payslip_number' => 'PS-2026-001',
            'pay_date' => '2026-09-30',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'gross_pay' => 5000.00,
            'total_deductions' => 800.00,
            'net_pay' => 4200.00,
            'currency' => 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Employee accessing own payslip -> 200 OK
        $response = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->getJson("/api/me/pay/payslips/{$payslipId}");

        $response->assertStatus(200);
        $this->assertEquals(4200, (float) $response->json('payslip.net_pay'));

        // 2. Other Employee attempting to access Employee A's payslip -> 403 Forbidden
        $unauthorized = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->otherEmployee->id,
        ])->getJson("/api/me/pay/payslips/{$payslipId}");

        $unauthorized->assertStatus(403);
    }

    public function test_document_policy_acknowledgement(): void
    {
        $catId = (string) Str::uuid();
        DB::table('hcm_document_categories')->insert([
            'id' => $catId,
            'tenant_id' => $this->tenant->id,
            'code' => 'POLICY_ACK',
            'name' => 'Company Policies',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $typeId = (string) Str::uuid();
        DB::table('hcm_document_types')->insert([
            'id' => $typeId,
            'tenant_id' => $this->tenant->id,
            'category_id' => $catId,
            'code' => 'SECURITY_POLICY',
            'name' => 'Security Policy',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $docId = (string) Str::uuid();
        DB::table('documents')->insert([
            'id' => $docId,
            'tenant_id' => $this->tenant->id,
            'title' => 'Security Policy 2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empDocId = (string) Str::uuid();
        DB::table('hcm_employee_documents')->insert([
            'id' => $empDocId,
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'document_type_id' => $typeId,
            'document_id' => $docId,
            'title' => 'Security Policy 2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reqId = (string) Str::uuid();
        DB::table('employee_document_requirements')->insert([
            'id' => $reqId,
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'document_type_id' => $typeId,
            'employee_document_id' => $empDocId,
            'is_mandatory' => true,
            'status' => 'required',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ackResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->postJson("/api/me/documents/{$reqId}/acknowledge");

        $ackResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify status is now submitted and an acknowledgement record exists
        $record = DB::table('employee_document_requirements')->where('id', $reqId)->first();
        $this->assertEquals('submitted', $record->status);

        $ack = DB::table('employee_document_acknowledgements')
            ->where('employee_document_id', $empDocId)
            ->where('employee_id', $this->employee->id)
            ->first();
        $this->assertNotNull($ack);
        $this->assertNotNull($ack->acknowledged_at);
    }

    public function test_employee_directory_and_search(): void
    {
        $dirResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->getJson('/api/me/directory?q=Fatima');

        $dirResponse->assertStatus(200)
            ->assertJsonCount(1, 'people')
            ->assertJsonPath('people.0.name', 'Fatima Khan');
    }

    public function test_ai_concierge_chat_grounded_in_employee_context(): void
    {
        $chatResponse = $this->withHeaders([
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
        ])->postJson('/api/me/ai/chat', [
            'prompt' => 'How much leave do I have remaining?',
        ]);

        $chatResponse->assertStatus(200)
            ->assertJsonStructure(['response', 'citations', 'employee_context']);

        $this->assertStringContainsString('leave', strtolower($chatResponse->json('response')));
        $this->assertEquals('Zain Ahmed', $chatResponse->json('employee_context.name'));
    }
}
