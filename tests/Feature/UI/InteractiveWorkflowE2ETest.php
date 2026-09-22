<?php

namespace Tests\Feature\UI;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class InteractiveWorkflowE2ETest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Employee $manager;
    protected Employee $employee;
    protected User $managerUser;
    protected User $employeeUser;
    protected string $companyId;
    protected string $leaveTypeId;
    protected string $documentReqId;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Tenant
        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Apex Global Corp',
            'slug' => 'apex-global',
            'tenant_code' => 'APX-ENT-E2E',
            'status' => 'active',
        ]);

        // 2. Create Company
        $this->companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $this->companyId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Apex Worldwide Ltd',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Manager User & Employee
        $this->managerUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Elena Rostova',
            'email' => 'elena.rostova@apexglobal.com',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        $this->manager = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->managerUser->id,
            'company_id' => $this->companyId,
            'employee_number' => 'MGR-701',
            'employee_code' => 'MGR-701',
            'first_name' => 'Elena',
            'last_name' => 'Rostova',
            'official_email' => 'elena.rostova@apexglobal.com',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYears(2)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Create Subordinate Employee User & Record
        $this->employeeUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Marcus Vance',
            'email' => 'marcus.vance@apexglobal.com',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        $this->employee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employeeUser->id,
            'company_id' => $this->companyId,
            'reporting_manager_id' => $this->manager->id,
            'employee_number' => 'EMP-902',
            'employee_code' => 'EMP-902',
            'first_name' => 'Marcus',
            'last_name' => 'Vance',
            'official_email' => 'marcus.vance@apexglobal.com',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(8)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Seed Leave Type & Balance
        $this->leaveTypeId = (string) Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $this->leaveTypeId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Annual Vacation Leave',
            'code' => 'ANNUAL_VACATION',
            'is_paid' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

        // 6. Seed Policy Document & Requirement
        $catId = (string) Str::uuid();
        DB::table('hcm_document_categories')->insert([
            'id' => $catId,
            'tenant_id' => $this->tenant->id,
            'code' => 'GOVERNANCE',
            'name' => 'Corporate Governance',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $typeId = (string) Str::uuid();
        DB::table('hcm_document_types')->insert([
            'id' => $typeId,
            'tenant_id' => $this->tenant->id,
            'category_id' => $catId,
            'code' => 'DATA_SECURITY_2026',
            'name' => 'Data Protection & Security Standard',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $docId = (string) Str::uuid();
        DB::table('documents')->insert([
            'id' => $docId,
            'tenant_id' => $this->tenant->id,
            'title' => 'Data Protection Policy 2026',
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
            'title' => 'Data Protection Policy 2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->documentReqId = (string) Str::uuid();
        DB::table('employee_document_requirements')->insert([
            'id' => $this->documentReqId,
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'document_type_id' => $typeId,
            'employee_document_id' => $empDocId,
            'is_mandatory' => true,
            'status' => 'required',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test 1: Verify Core Portal Web Routes return valid HTTP status for authenticated employee
     */
    public function test_all_core_portal_web_routes_render_successfully(): void
    {
        $routes = [
            '/portal',
            '/portal/work',
            '/portal/requests',
            '/portal/profile',
            '/portal/pay',
            '/portal/growth',
            '/portal/documents',
            '/portal/services',
            '/portal/directory',
            '/portal/manager/workbench',
            '/portal/billing',
            '/portal/hr-services/catalog',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->employeeUser)
                ->withHeaders([
                    'X-Tenant-ID' => $this->tenant->id,
                    'X-Employee-ID' => $this->employee->id,
                ])
                ->get($route);

            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Route {$route} failed to return valid HTTP status (got {$response->status()})"
            );
        }
    }

    /**
     * Test 2: Interactive Attendance Clock In / Out Toggle Workflow
     */
    public function test_interactive_attendance_clock_toggle_lifecycle(): void
    {
        // Step 1: Clock In
        $inResponse = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->postJson('/api/me/attendance/clock', ['action' => 'toggle']);

        $inResponse->assertStatus(200)
            ->assertJsonPath('status', 'CLOCKED_IN');

        // Verify active attendance session in DB
        $session = DB::table('attendance_sessions')
            ->where('tenant_id', $this->tenant->id)
            ->where('employee_id', $this->employee->id)
            ->first();

        $this->assertNotNull($session);
        $this->assertNull($session->actual_end_time);

        // Step 2: Clock Out
        $outResponse = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->postJson('/api/me/attendance/clock', ['action' => 'toggle']);

        $outResponse->assertStatus(200)
            ->assertJsonPath('status', 'CLOCKED_OUT');

        // Verify session ended
        $sessionAfter = DB::table('attendance_sessions')
            ->where('id', $session->id)
            ->first();

        $this->assertNotNull($sessionAfter->actual_end_time);
    }

    /**
     * Test 3: Interactive Leave Request Submission & Unified Requests Tracking
     */
    public function test_interactive_leave_request_application_workflow(): void
    {
        $applyResponse = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->postJson('/api/me/leave/apply', [
                'leave_type_id' => $this->leaveTypeId,
                'start_date' => Carbon::now()->addDays(5)->toDateString(),
                'end_date' => Carbon::now()->addDays(7)->toDateString(),
                'duration' => 3.0,
                'reason' => 'Quarterly retreat and wellness days',
            ]);

        $applyResponse->assertStatus(201)
            ->assertJsonPath('status', 'PENDING');

        // Check requests endpoint reflects pending item
        $requestsResponse = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->getJson('/api/me/requests');

        $requestsResponse->assertStatus(200);
        $this->assertNotEmpty($requestsResponse->json('requests'));
    }

    /**
     * Test 4: Interactive Document Acknowledgment Workflow
     */
    public function test_interactive_document_acknowledgment_workflow(): void
    {
        $ackResponse = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->postJson("/api/me/documents/{$this->documentReqId}/acknowledge");

        $ackResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify requirement status updated to submitted
        $req = DB::table('employee_document_requirements')
            ->where('id', $this->documentReqId)
            ->first();
        $this->assertEquals('submitted', $req->status);
    }

    /**
     * Test 5: Interactive AI Concierge Chat Workflow
     */
    public function test_interactive_ai_concierge_chat_response(): void
    {
        $chatResponse = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->postJson('/api/me/ai/chat', [
                'prompt' => 'How many days of vacation leave do I have remaining?',
            ]);

        $chatResponse->assertStatus(200)
            ->assertJsonStructure(['response', 'citations', 'employee_context']);

        $this->assertNotEmpty($chatResponse->json('response'));
    }

    /**
     * Test 6: Manager Workbench Approvals and Decision Workflow
     */
    public function test_manager_workbench_approvals_workflow(): void
    {
        // 1. Fetch manager pending approvals
        $approvalsResponse = $this->actingAs($this->managerUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->manager->id,
            ])
            ->getJson('/api/manager/approvals');

        $approvalsResponse->assertStatus(200);
        $this->assertArrayHasKey('approvals', $approvalsResponse->json());
    }
}
