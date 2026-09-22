<?php

declare(strict_types=1);

namespace Tests\Feature\HCM;

use App\Domains\Shared\Models\Tenant;
use App\Domains\Shared\Services\AuditRedactor;
use App\Domains\Shared\Services\AuditService;
use App\Domains\Shared\Services\HcmWorkflowOrchestrationService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class HcmCrossDomainWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected string $tenantId;
    protected string $companyId;
    protected string $buId;
    protected string $deptId;
    protected HcmWorkflowOrchestrationService $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Apex Global Technologies',
            'slug' => 'apex-global-test',
            'tenant_code' => 'APX-TEST',
            'status' => 'active',
        ]);
        $this->tenantId = (string) $this->tenant->id;

        $this->companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $this->companyId,
            'tenant_id' => $this->tenantId,
            'name' => 'Apex Global Technologies Inc.',
            'legal_name' => 'Apex Global Technologies International Corp.',
            'timezone' => 'America/New_York',
            'currency' => 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->buId = (string) Str::uuid();
        DB::table('business_units')->insert([
            'id' => $this->buId,
            'tenant_id' => $this->tenantId,
            'company_id' => $this->companyId,
            'code' => 'BU-ENG',
            'name' => 'Engineering Division',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deptId = (string) Str::uuid();
        DB::table('departments')->insert([
            'id' => $this->deptId,
            'tenant_id' => $this->tenantId,
            'business_unit_id' => $this->buId,
            'department_code' => 'DEP-ENG',
            'department_name' => 'Software Engineering',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $auditService = new AuditService(new AuditRedactor());
        $this->orchestrator = new HcmWorkflowOrchestrationService($auditService);
    }

    /**
     * Helper to create a position
     */
    protected function createPosition(string $title = 'Software Engineer', int $headcount = 2, int $filled = 0): string
    {
        $id = (string) Str::uuid();
        DB::table('positions')->insert([
            'id' => $id,
            'tenant_id' => $this->tenantId,
            'code' => 'POS-' . strtoupper(Str::random(5)),
            'title' => $title,
            'department_id' => $this->deptId,
            'headcount' => $headcount,
            'filled_headcount' => $filled,
            'status' => 'active',
            'salary_min' => 80000.00,
            'salary_max' => 120000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $id;
    }

    /**
     * Helper to create an employee
     */
    protected function createEmployee(string $firstName = 'John', string $lastName = 'Doe', ?string $positionId = null): string
    {
        $id = (string) Str::uuid();
        $code = 'EMP-' . strtoupper(Str::random(5));
        DB::table('employees')->insert([
            'id' => $id,
            'tenant_id' => $this->tenantId,
            'company_id' => $this->companyId,
            'employee_number' => $code,
            'employee_code' => $code,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'official_email' => strtolower("{$firstName}.{$lastName}@apex.test"),
            'department_id' => $this->deptId,
            'current_position_id' => $positionId,
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
            'original_hire_date' => now()->toDateString(),
            'portal_access' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $id;
    }

    /**
     * WORKFLOW A: Candidate to Hired Employee
     */
    public function test_workflow_a_candidate_to_hired_employee(): void
    {
        $positionId = $this->createPosition('Cloud Platform Architect', 2, 0);

        $result = $this->orchestrator->hireCandidateToEmployee($this->tenantId, [
            'position_id' => $positionId,
            'department_id' => $this->deptId,
            'company_id' => $this->companyId,
            'first_name' => 'Alice',
            'last_name' => 'Walker',
            'initial_salary' => 125000.00,
            'currency' => 'USD',
        ], 1);

        $this->assertNotNull($result['employee_id']);
        $this->assertEquals('active', $result['status']);

        // Check employee record
        $employee = DB::table('employees')->where('id', $result['employee_id'])->first();
        $this->assertNotNull($employee);
        $this->assertEquals('Alice', $employee->first_name);
        $this->assertEquals($this->tenantId, $employee->tenant_id);

        // Check position occupied
        $position = DB::table('positions')->where('id', $positionId)->first();
        $this->assertEquals(1, (int) $position->filled_headcount);

        // Check position occupancy record
        $this->assertDatabaseHas('position_occupancies', [
            'position_id' => $positionId,
            'employee_id' => $result['employee_id'],
        ]);

        // Check compensation record
        $this->assertDatabaseHas('employee_compensations', [
            'employee_id' => $result['employee_id'],
            'base_salary' => 125000.00,
            'is_active' => 1,
        ]);

        // Check audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_HIRE_SUCCESS',
            'entity_id' => $result['employee_id'],
        ]);
    }

    /**
     * WORKFLOW A (Capacity Failure Mode): Over-allocation Prevention
     */
    public function test_workflow_a_fails_when_position_at_maximum_capacity(): void
    {
        $positionId = $this->createPosition('Junior Analyst', 1, 1); // 1 headcount, already 1 filled

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('maximum headcount capacity');

        $this->orchestrator->hireCandidateToEmployee($this->tenantId, [
            'position_id' => $positionId,
            'first_name' => 'Bob',
            'last_name' => 'Ross',
        ]);
    }

    /**
     * WORKFLOW B: Leave Request to Balance Deduction
     */
    public function test_workflow_b_leave_request_deduction_and_calendar_blocking(): void
    {
        $empId = $this->createEmployee('Sarah', 'Jenkins');
        $leaveTypeId = (string) Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $leaveTypeId,
            'tenant_id' => $this->tenantId,
            'name' => 'Annual Paid Vacation',
            'code' => 'VACATION',
            'is_paid' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed 15 days entitlement
        $balanceId = (string) Str::uuid();
        DB::table('leave_balances')->insert([
            'id' => $balanceId,
            'tenant_id' => $this->tenantId,
            'employee_id' => $empId,
            'leave_type_id' => $leaveTypeId,
            'year' => (int) date('Y'),
            'entitled' => 15.0,
            'earned' => 15.0,
            'used' => 0.0,
            'pending' => 0.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Request 3 days leave
        $reqResult = $this->orchestrator->processEmployeeLeaveRequest($this->tenantId, [
            'employee_id' => $empId,
            'leave_type_id' => $leaveTypeId,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
            'duration' => 3.0,
            'auto_approve' => false,
        ]);

        $this->assertEquals('pending', $reqResult['status']);

        // Check balance pending incremented
        $bal = DB::table('leave_balances')->where('id', $balanceId)->first();
        $this->assertEquals(3.0, (float) $bal->pending);

        // Approve leave
        $apprResult = $this->orchestrator->approveLeaveRequest($this->tenantId, $reqResult['leave_application_id']);
        $this->assertEquals('approved', $apprResult['status']);

        // Verify balance committed (pending=0, used=3)
        $balAfter = DB::table('leave_balances')->where('id', $balanceId)->first();
        $this->assertEquals(0.0, (float) $balAfter->pending);
        $this->assertEquals(3.0, (float) $balAfter->used);

        // Verify audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_LEAVE_APPR',
            'entity_id' => $reqResult['leave_application_id'],
        ]);
    }

    /**
     * WORKFLOW C: Attendance to Payroll Aggregation
     */
    public function test_workflow_c_attendance_to_payroll_aggregation(): void
    {
        $empId = $this->createEmployee('Michael', 'Scott');
        $payrollPeriodId = (string) Str::uuid();

        $result = $this->orchestrator->aggregateAttendanceToPayrollInput($this->tenantId, [
            'employee_id' => $empId,
            'payroll_period_id' => $payrollPeriodId,
            'regular_minutes' => 160 * 60,
            'overtime_minutes' => 15 * 60,
        ], 1);

        $this->assertEquals('locked_for_payroll', $result['status']);
        $this->assertEquals(160.0, $result['regular_hours']);
        $this->assertEquals(15.0, $result['overtime_hours']);

        // Verify timesheet approved
        $this->assertDatabaseHas('timesheets', [
            'id' => $result['timesheet_id'],
            'employee_id' => $empId,
            'status' => 'approved',
        ]);

        // Verify payroll_inputs
        $this->assertDatabaseHas('payroll_inputs', [
            'id' => $result['payroll_input_id'],
            'employee_id' => $empId,
            'status' => 'collected',
        ]);

        // Verify audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_TIME_PAYROLL',
        ]);
    }

    /**
     * WORKFLOW D: Compensation Adjustment to Payroll Impact
     */
    public function test_workflow_d_compensation_adjustment_to_payroll_impact(): void
    {
        $empId = $this->createEmployee('Dwight', 'Schrute');

        // Initial comp
        $initialCompId = (string) Str::uuid();
        DB::table('employee_compensations')->insert([
            'id' => $initialCompId,
            'tenant_id' => $this->tenantId,
            'employee_id' => $empId,
            'currency' => 'USD',
            'pay_frequency' => 'monthly',
            'base_salary' => 70000.00,
            'gross_salary' => 70000.00,
            'effective_from' => '2025-01-01',
            'is_active' => 1,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Adjust compensation
        $result = $this->orchestrator->applyCompensationChange($this->tenantId, [
            'employee_id' => $empId,
            'base_salary' => 85000.00,
            'effective_from' => '2026-06-01',
            'reason_for_change' => 'Mid-Year Merit Uplift',
        ], 1);

        $this->assertEquals(70000.00, $result['previous_salary']);
        $this->assertEquals(85000.00, $result['new_salary']);

        // Previous comp must be deactivated
        $prev = DB::table('employee_compensations')->where('id', $initialCompId)->first();
        $this->assertEquals(0, $prev->is_active);

        // New comp must be active
        $this->assertDatabaseHas('employee_compensations', [
            'id' => $result['compensation_id'],
            'base_salary' => 85000.00,
            'is_active' => 1,
        ]);

        // Audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_COMP_ADJUST',
        ]);
    }

    /**
     * WORKFLOW E: Learning Completion to Skill Profile & Compliance
     */
    public function test_workflow_e_learning_completion_to_skill_profile(): void
    {
        $empId = $this->createEmployee('Jim', 'Halpert');
        $courseId = (string) Str::uuid();
        $skillId = (string) Str::uuid();

        DB::table('career_skills')->insert([
            'id' => $skillId,
            'uuid' => $skillId,
            'tenant_id' => $this->tenantId,
            'code' => 'SKILL-AWS-01',
            'name' => 'AWS Solutions Architecture',
            'status' => 'active',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('courses')->insert([
            'id' => $courseId,
            'tenant_id' => $this->tenantId,
            'code' => 'CRS-AWS-01',
            'name' => 'AWS Cloud Solutions',
            'delivery_type' => 'online',
            'passing_score' => 75,
            'is_mandatory' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->orchestrator->completeCourseAndAwardSkill($this->tenantId, [
            'employee_id' => $empId,
            'course_id' => $courseId,
            'skill_id' => $skillId,
            'score' => 92.5,
        ], 1);

        $this->assertEquals('certified', $result['status']);
        $this->assertEquals(3, $result['skill_level']);

        // Verify enrollment completed
        $this->assertDatabaseHas('course_enrollments', [
            'id' => $result['enrollment_id'],
            'employee_id' => $empId,
            'status' => 'completed',
            'progress' => 100,
        ]);

        // Verify skill profile
        $this->assertDatabaseHas('employee_skills', [
            'employee_id' => $empId,
            'skill_id' => $skillId,
            'verification_status' => 'verified',
        ]);

        // Audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_LMS_COMPLETE',
        ]);
    }

    /**
     * WORKFLOW F: Performance Appraisal to Goal Progression & Talent Rating
     */
    public function test_workflow_f_performance_appraisal_to_talent_pool(): void
    {
        $empId = $this->createEmployee('Pam', 'Beesly');
        $cycleId = (string) Str::uuid();
        $poolId = (string) Str::uuid();

        DB::table('performance_cycles')->insert([
            'id' => $cycleId,
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantId,
            'name' => '2026 Annual Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('talent_pools')->insert([
            'id' => $poolId,
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantId,
            'code' => 'POOL-EXECUTIVE',
            'name' => 'Executive Talent Bench',
            'status' => 'active',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->orchestrator->finalizePerformanceReview($this->tenantId, [
            'employee_id' => $empId,
            'performance_cycle_id' => $cycleId,
            'rating_score' => 4.8,
            'talent_pool_id' => $poolId,
        ], 1);

        $this->assertEquals('calibrated_and_certified', $result['status']);
        $this->assertNotNull($result['talent_pool_member_id']);

        // Check talent pool membership
        $this->assertDatabaseHas('talent_pool_members', [
            'pool_id' => $poolId,
            'employee_id' => $empId,
            'status' => 'active',
        ]);

        // Audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_PERF_FINALIZE',
        ]);
    }

    /**
     * WORKFLOW G: Expense Submission to Multi-level Approval & Payroll Reimbursement
     */
    public function test_workflow_g_expense_submission_to_payroll_reimbursement(): void
    {
        $empId = $this->createEmployee('Stanley', 'Hudson');

        $result = $this->orchestrator->processExpenseClaimAndReimbursement($this->tenantId, [
            'employee_id' => $empId,
            'amount' => 450.75,
            'currency' => 'USD',
        ], 1);

        $this->assertEquals('approved_and_queued', $result['status']);
        $this->assertEquals(450.75, $result['approved_total']);

        // Check expense claim
        $this->assertDatabaseHas('expense_claims', [
            'id' => $result['expense_claim_id'],
            'employee_id' => $empId,
            'status' => 'approved',
            'approved_total' => 450.75,
        ]);

        // Check queued in payroll_inputs
        $this->assertDatabaseHas('payroll_inputs', [
            'id' => $result['payroll_input_id'],
            'employee_id' => $empId,
            'status' => 'reimbursement_queued',
        ]);

        // Audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_EXPENSE_SETTLE',
        ]);
    }

    /**
     * WORKFLOW H: Department / Location Transfer with Reporting Line Reassignment
     */
    public function test_workflow_h_department_transfer_with_reporting_line(): void
    {
        $empId = $this->createEmployee('Toby', 'Flenderson');
        $managerId = $this->createEmployee('Corporate', 'VP');

        $newDeptId = (string) Str::uuid();
        DB::table('departments')->insert([
            'id' => $newDeptId,
            'tenant_id' => $this->tenantId,
            'business_unit_id' => $this->buId,
            'department_code' => 'DEP-FIN',
            'department_name' => 'Corporate Finance',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->orchestrator->transferEmployee($this->tenantId, [
            'employee_id' => $empId,
            'new_department_id' => $newDeptId,
            'new_manager_id' => $managerId,
        ], 1);

        $this->assertEquals('transferred', $result['status']);

        // Check employee updated
        $emp = DB::table('employees')->where('id', $empId)->first();
        $this->assertEquals($newDeptId, $emp->department_id);
        $this->assertEquals($managerId, $emp->current_manager_employee_id);

        // Audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_EMP_TRANSFER',
        ]);
    }

    /**
     * WORKFLOW I: Employee Promotion with Grade, Title & Compensation
     */
    public function test_workflow_i_employee_promotion_with_grade_and_compensation(): void
    {
        $oldPosId = $this->createPosition('Staff Engineer', 1, 1);
        $newPosId = $this->createPosition('Engineering Director', 1, 0);

        $empId = $this->createEmployee('Andy', 'Bernard', $oldPosId);

        // Current comp
        DB::table('employee_compensations')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantId,
            'employee_id' => $empId,
            'currency' => 'USD',
            'pay_frequency' => 'monthly',
            'base_salary' => 110000.00,
            'gross_salary' => 110000.00,
            'effective_from' => '2025-01-01',
            'is_active' => 1,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->orchestrator->promoteEmployee($this->tenantId, [
            'employee_id' => $empId,
            'new_position_id' => $newPosId,
            'new_salary' => 145000.00,
            'reason' => 'Promotion to Leadership',
        ], 1);

        $this->assertEquals('promoted', $result['status']);

        // Old position vacated (filled_headcount 0)
        $oldPos = DB::table('positions')->where('id', $oldPosId)->first();
        $this->assertEquals(0, (int) $oldPos->filled_headcount);

        // New position occupied (filled_headcount 1)
        $newPos = DB::table('positions')->where('id', $newPosId)->first();
        $this->assertEquals(1, (int) $newPos->filled_headcount);

        // Employee position updated
        $emp = DB::table('employees')->where('id', $empId)->first();
        $this->assertEquals($newPosId, $emp->current_position_id);

        // Active compensation updated
        $this->assertDatabaseHas('employee_compensations', [
            'employee_id' => $empId,
            'base_salary' => 145000.00,
            'is_active' => 1,
        ]);

        // Audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_EMP_PROMOTE',
        ]);
    }

    /**
     * WORKFLOW J: Employee Resignation / Termination to Clearance & Offboarding
     */
    public function test_workflow_j_employee_separation_clearance_and_offboarding(): void
    {
        $posId = $this->createPosition('Product Manager', 2, 1);
        $empId = $this->createEmployee('Kevin', 'Malone', $posId);

        $result = $this->orchestrator->separateEmployee($this->tenantId, [
            'employee_id' => $empId,
            'separation_type' => 'resignation',
            'last_working_date' => now()->addDays(14)->toDateString(),
            'reason' => 'Relocation',
        ], 1);

        $this->assertEquals('separated', $result['status']);

        // Separation case created
        $this->assertDatabaseHas('separation_cases', [
            'id' => $result['separation_case_id'],
            'employee_id' => $empId,
            'status' => 'approved',
        ]);

        // Clearance checklist tasks created (IT, HR, Finance)
        $tasksCount = DB::table('exit_checklist_tasks')->where('separation_case_id', $result['separation_case_id'])->count();
        $this->assertEquals(3, $tasksCount);

        // Position vacated
        $pos = DB::table('positions')->where('id', $posId)->first();
        $this->assertEquals(0, (int) $pos->filled_headcount);

        // Employee status terminated and portal access revoked
        $emp = DB::table('employees')->where('id', $empId)->first();
        $this->assertEquals('terminated', $emp->employment_status);
        $this->assertNull($emp->current_position_id);
        $this->assertEquals(0, $emp->portal_access);

        // Audit event
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantId,
            'event_type' => 'HCM_WF_EMP_OFFBOARD',
            'entity_id' => $result['separation_case_id'],
        ]);
    }
}
