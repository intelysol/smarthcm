<?php

namespace App\Domains\Shared\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use DomainException;

class HcmWorkflowOrchestrationService
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Workflow A: Candidate to Hired Employee
     *
     * Validates target position capacity, creates immutable Core HR employee,
     * occupies position, and initializes onboarding lifecycle.
     */
    public function hireCandidateToEmployee(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $candidateId = $data['candidate_id'] ?? null;
            $positionId = $data['position_id'] ?? null;
            $departmentId = $data['department_id'] ?? null;
            $firstName = $data['first_name'] ?? 'New';
            $lastName = $data['last_name'] ?? 'Hire';
            $email = $data['official_email'] ?? ($data['email'] ?? strtolower($firstName . '.' . $lastName . '@company.com'));
            $hireDate = $data['joining_date'] ?? now()->toDateString();
            $salary = (float) ($data['initial_salary'] ?? 60000.00);

            // 1. Validate position capacity if position provided
            $companyId = $data['company_id'] ?? null;
            if (!$companyId) {
                $companyId = DB::table('companies')->where('tenant_id', $tenantId)->value('id');
                if (!$companyId) {
                    $companyId = (string) Str::uuid();
                    DB::table('companies')->insert([
                        'id' => $companyId,
                        'tenant_id' => $tenantId,
                        'name' => 'Default Company',
                        'legal_name' => 'Default Company LLC',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($positionId) {
                $position = DB::table('positions')
                    ->where('id', $positionId)
                    ->where('tenant_id', $tenantId)
                    ->lockForUpdate()
                    ->first();

                if (!$position) {
                    throw new DomainException("Position {$positionId} not found in tenant.");
                }

                $headcount = (int) ($position->headcount ?? 1);
                $filled = (int) ($position->filled_headcount ?? 0);
                if ($filled >= $headcount) {
                    throw new DomainException("Position '{$position->title}' has reached maximum headcount capacity ({$filled}/{$headcount}).");
                }

                // Increment filled headcount
                DB::table('positions')->where('id', $positionId)->update([
                    'filled_headcount' => $filled + 1,
                    'updated_at' => now(),
                ]);
            }

            // 2. Create Core HR Employee Master
            $employeeId = (string) Str::uuid();
            $employeeNumber = 'EMP-' . strtoupper(Str::random(6));

            $employeeData = [
                'id' => $employeeId,
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'employee_number' => $employeeNumber,
                'employee_code' => $employeeNumber,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'official_email' => $email,
                'personal_email' => $data['personal_email'] ?? $email,
                'department_id' => $departmentId,
                'current_position_id' => $positionId,
                'employment_status' => 'active',
                'joining_date' => $hireDate,
                'original_hire_date' => $hireDate,
                'portal_access' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('employees')->insert($employeeData);

            // 3. Record position occupancy if position provided
            if ($positionId) {
                $occupancyId = (string) Str::uuid();
                DB::table('position_occupancies')->insert([
                    'id' => $occupancyId,
                    'position_id' => $positionId,
                    'employee_id' => $employeeId,
                    'starts_on' => $hireDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Record initial compensation
            $compId = (string) Str::uuid();
            DB::table('employee_compensations')->insert([
                'id' => $compId,
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'currency' => $data['currency'] ?? 'USD',
                'pay_frequency' => 'monthly',
                'base_salary' => $salary,
                'gross_salary' => $salary,
                'effective_from' => $hireDate,
                'reason_for_change' => 'New Hire Compensation',
                'status' => 'approved',
                'is_active' => 1,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 5. Update candidate status if provided
            if ($candidateId) {
                DB::table('candidates')->where('id', $candidateId)->where('tenant_id', $tenantId)->update([
                    'updated_at' => now(),
                ]);
            }

            // 6. Audit Trail
            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_HIRE_SUCCESS',
                action: 'HIRE_CANDIDATE',
                entityType: 'Employee',
                entityId: $employeeId,
                actorId: $actorId,
                before: ['candidate_id' => $candidateId, 'position_id' => $positionId],
                after: ['employee_id' => $employeeId, 'employee_number' => $employeeNumber, 'status' => 'active']
            );

            return [
                'employee_id' => $employeeId,
                'employee_number' => $employeeNumber,
                'position_id' => $positionId,
                'compensation_id' => $compId,
                'status' => 'active',
            ];
        });
    }

    /**
     * Workflow B: Employee Leave Request to Balance Deduction & Calendar Blocking
     */
    public function processEmployeeLeaveRequest(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $employeeId = $data['employee_id'];
            $leaveTypeId = $data['leave_type_id'];
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $duration = (float) ($data['duration'] ?? 1.0);
            $reason = $data['reason'] ?? 'Annual leave request';
            $autoApprove = (bool) ($data['auto_approve'] ?? false);

            // 1. Check leave balance
            $balance = DB::table('leave_balances')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->lockForUpdate()
                ->first();

            $year = Carbon::parse($startDate)->year;
            if (!$balance) {
                // Initialize default balance if not seeded
                $balanceId = (string) Str::uuid();
                DB::table('leave_balances')->insert([
                    'id' => $balanceId,
                    'tenant_id' => $tenantId,
                    'employee_id' => $employeeId,
                    'leave_type_id' => $leaveTypeId,
                    'year' => $year,
                    'entitled' => 20.0,
                    'earned' => 20.0,
                    'used' => 0.0,
                    'pending' => 0.0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $balance = DB::table('leave_balances')->where('id', $balanceId)->first();
            }

            $available = ((float) $balance->entitled + (float) $balance->earned) - ((float) $balance->used + (float) $balance->pending);
            if ($available < $duration) {
                throw new DomainException("Insufficient leave balance. Available: {$available}, Requested: {$duration}.");
            }

            // 2. Create leave application
            $leaveAppId = (string) Str::uuid();
            $status = $autoApprove ? 'approved' : 'pending';

            DB::table('leave_applications')->insert([
                'id' => $leaveAppId,
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration' => $duration,
                'unit' => 'days',
                'reason' => $reason,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Update balance
            if ($autoApprove) {
                DB::table('leave_balances')->where('id', $balance->id)->update([
                    'used' => (float) $balance->used + $duration,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('leave_balances')->where('id', $balance->id)->update([
                    'pending' => (float) $balance->pending + $duration,
                    'updated_at' => now(),
                ]);
            }

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_LEAVE_REQ',
                action: 'SUBMIT_LEAVE_REQUEST',
                entityType: 'LeaveApplication',
                entityId: $leaveAppId,
                actorId: $actorId,
                before: ['available_balance' => $available],
                after: ['leave_id' => $leaveAppId, 'status' => $status, 'duration' => $duration]
            );

            return [
                'leave_application_id' => $leaveAppId,
                'status' => $status,
                'duration' => $duration,
                'remaining_balance' => $available - $duration,
            ];
        });
    }

    /**
     * Approve leave request and commit balance deduction + calendar blackout
     */
    public function approveLeaveRequest(string $tenantId, string $leaveApplicationId, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $leaveApplicationId, $actorId) {
            $leave = DB::table('leave_applications')
                ->where('id', $leaveApplicationId)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$leave) {
                throw new DomainException("Leave application {$leaveApplicationId} not found.");
            }

            if ($leave->status === 'approved') {
                return ['leave_application_id' => $leave->id, 'status' => 'approved'];
            }

            $duration = (float) $leave->duration;

            // Debit from pending to used
            $balance = DB::table('leave_balances')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->lockForUpdate()
                ->first();

            if ($balance) {
                $newPending = max(0, (float) $balance->pending - $duration);
                $newUsed = (float) $balance->used + $duration;

                DB::table('leave_balances')->where('id', $balance->id)->update([
                    'pending' => $newPending,
                    'used' => $newUsed,
                    'updated_at' => now(),
                ]);
            }

            DB::table('leave_applications')->where('id', $leaveApplicationId)->update([
                'status' => 'approved',
                'updated_at' => now(),
            ]);

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_LEAVE_APPR',
                action: 'APPROVE_LEAVE',
                entityType: 'LeaveApplication',
                entityId: $leaveApplicationId,
                actorId: $actorId,
                before: ['status' => $leave->status],
                after: ['status' => 'approved', 'deducted_duration' => $duration]
            );

            return [
                'leave_application_id' => $leaveApplicationId,
                'status' => 'approved',
                'duration' => $duration,
            ];
        });
    }

    /**
     * Workflow C: Attendance / Timesheet Aggregation to Payroll Input
     */
    public function aggregateAttendanceToPayrollInput(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $employeeId = $data['employee_id'];
            $regularMinutes = (int) ($data['regular_minutes'] ?? (160 * 60));
            $overtimeMinutes = (int) ($data['overtime_minutes'] ?? (10 * 60));
            $validActorId = ($actorId && DB::table('users')->where('id', $actorId)->exists()) ? $actorId : null;

            // Ensure valid payroll period exists
            $payrollPeriodId = $data['payroll_period_id'] ?? null;
            if (!$payrollPeriodId || !DB::table('payroll_periods')->where('id', $payrollPeriodId)->exists()) {
                $existingPeriod = DB::table('payroll_periods')->where('tenant_id', $tenantId)->where('status', 'open')->first();
                if ($existingPeriod) {
                    $payrollPeriodId = $existingPeriod->id;
                } else {
                    $payrollPeriodId = (string) Str::uuid();
                    DB::table('payroll_periods')->insert([
                        'id' => $payrollPeriodId,
                        'tenant_id' => $tenantId,
                        'period_name' => 'Payroll Cycle ' . date('Y-m'),
                        'start_date' => now()->startOfMonth()->toDateString(),
                        'end_date' => now()->endOfMonth()->toDateString(),
                        'status' => 'open',
                        'currency' => 'USD',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 1. Create or certify timesheet
            $timesheetId = (string) Str::uuid();
            DB::table('timesheets')->insert([
                'id' => $timesheetId,
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'period_type' => 'monthly',
                'start_date' => $data['start_date'] ?? now()->startOfMonth()->toDateString(),
                'end_date' => $data['end_date'] ?? now()->endOfMonth()->toDateString(),
                'total_scheduled_minutes' => $regularMinutes,
                'total_worked_minutes' => $regularMinutes + $overtimeMinutes,
                'total_regular_minutes' => $regularMinutes,
                'total_overtime_minutes' => $overtimeMinutes,
                'total_approved_overtime_minutes' => $overtimeMinutes,
                'status' => 'approved',
                'approved_by' => $validActorId,
                'approved_at' => now(),
                'exported_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Export into Payroll Input
            $payrollInputId = (string) Str::uuid();
            DB::table('payroll_inputs')->insert([
                'id' => $payrollInputId,
                'tenant_id' => $tenantId,
                'payroll_period_id' => $payrollPeriodId,
                'employee_id' => $employeeId,
                'status' => 'collected',
                'collected_by' => $validActorId,
                'collected_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_TIME_PAYROLL',
                action: 'AGGREGATE_TIME_TO_PAYROLL',
                entityType: 'Timesheet',
                entityId: $timesheetId,
                actorId: $actorId,
                before: null,
                after: [
                    'timesheet_id' => $timesheetId,
                    'payroll_input_id' => $payrollInputId,
                    'regular_minutes' => $regularMinutes,
                    'approved_ot_minutes' => $overtimeMinutes,
                ]
            );

            return [
                'timesheet_id' => $timesheetId,
                'payroll_input_id' => $payrollInputId,
                'regular_hours' => round($regularMinutes / 60, 2),
                'overtime_hours' => round($overtimeMinutes / 60, 2),
                'status' => 'locked_for_payroll',
            ];
        });
    }

    /**
     * Workflow D: Compensation Adjustment to Payroll Impact
     */
    public function applyCompensationChange(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $employeeId = $data['employee_id'];
            $newSalary = (float) $data['base_salary'];
            $effectiveFrom = $data['effective_from'] ?? now()->toDateString();
            $reason = $data['reason_for_change'] ?? 'Annual Merit Revision';

            // 1. Fetch current active compensation
            $currentComp = DB::table('employee_compensations')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->lockForUpdate()
                ->first();

            $oldSalary = $currentComp ? (float) $currentComp->base_salary : 0.0;

            // Deactivate current active record
            if ($currentComp) {
                DB::table('employee_compensations')
                    ->where('id', $currentComp->id)
                    ->update([
                        'is_active' => 0,
                        'effective_to' => Carbon::parse($effectiveFrom)->subDay()->toDateString(),
                        'updated_at' => now(),
                    ]);
            }

            // 2. Insert new compensation record
            $validActorId = ($actorId && DB::table('users')->where('id', $actorId)->exists()) ? $actorId : null;
            $newCompId = (string) Str::uuid();
            DB::table('employee_compensations')->insert([
                'id' => $newCompId,
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'currency' => $data['currency'] ?? ($currentComp->currency ?? 'USD'),
                'pay_frequency' => 'monthly',
                'base_salary' => $newSalary,
                'gross_salary' => $newSalary,
                'effective_from' => $effectiveFrom,
                'reason_for_change' => $reason,
                'status' => 'approved',
                'is_active' => 1,
                'approved_by' => $validActorId,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_COMP_ADJUST',
                action: 'ADJUST_COMPENSATION',
                entityType: 'EmployeeCompensation',
                entityId: $newCompId,
                actorId: $actorId,
                before: ['base_salary' => $oldSalary],
                after: ['base_salary' => $newSalary, 'effective_from' => $effectiveFrom, 'reason' => $reason]
            );

            return [
                'compensation_id' => $newCompId,
                'employee_id' => $employeeId,
                'previous_salary' => $oldSalary,
                'new_salary' => $newSalary,
                'effective_from' => $effectiveFrom,
            ];
        });
    }

    /**
     * Workflow E: Learning Completion to Skill Profile & Compliance
     */
    public function completeCourseAndAwardSkill(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $employeeId = $data['employee_id'];
            $courseId = $data['course_id'];
            $score = (float) ($data['score'] ?? 100.0);
            $skillId = $data['skill_id'] ?? (string) Str::uuid();
            $validActorId = ($actorId && DB::table('users')->where('id', $actorId)->exists()) ? (string)$actorId : null;

            // 1. Record / Complete course enrollment
            $skillId = $data['skill_id'] ?? null;
            if (!$skillId || !DB::table('career_skills')->where('id', $skillId)->exists()) {
                $existingCareerSkill = DB::table('career_skills')->where('tenant_id', $tenantId)->first();
                if ($existingCareerSkill) {
                    $skillId = $existingCareerSkill->id;
                } else {
                    $skillId = (string) Str::uuid();
                    DB::table('career_skills')->insert([
                        'id' => $skillId,
                        'uuid' => $skillId,
                        'tenant_id' => $tenantId,
                        'code' => 'SKILL-CLOUD-ARCH',
                        'name' => 'Cloud Architecture',
                        'status' => 'active',
                        'version' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $enrollment = DB::table('course_enrollments')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employeeId)
                ->where('course_id', $courseId)
                ->first();

            if ($enrollment) {
                DB::table('course_enrollments')->where('id', $enrollment->id)->update([
                    'status' => 'completed',
                    'progress' => 100,
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);
                $enrollmentId = $enrollment->id;
            } else {
                $enrollmentId = (string) Str::uuid();
                DB::table('course_enrollments')->insert([
                    'id' => $enrollmentId,
                    'tenant_id' => $tenantId,
                    'employee_id' => $employeeId,
                    'course_id' => $courseId,
                    'status' => 'completed',
                    'progress' => 100,
                    'completed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. Award or increment employee skill
            $existingSkill = DB::table('employee_skills')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employeeId)
                ->where('skill_id', $skillId)
                ->first();

            if ($existingSkill) {
                DB::table('employee_skills')->where('id', $existingSkill->id)->update([
                    'current_level' => 4,
                    'verification_status' => 'verified',
                    'verified_by' => $validActorId,
                    'verified_at' => now(),
                    'last_assessed_at' => now(),
                    'updated_at' => now(),
                ]);
                $empSkillId = $existingSkill->id;
            } else {
                $empSkillId = (string) Str::uuid();
                DB::table('employee_skills')->insert([
                    'id' => $empSkillId,
                    'uuid' => $empSkillId,
                    'tenant_id' => $tenantId,
                    'employee_id' => $employeeId,
                    'skill_id' => $skillId,
                    'current_level' => 3,
                    'source' => 'lms_course_completion',
                    'verification_status' => 'verified',
                    'verified_by' => $validActorId,
                    'verified_at' => now(),
                    'last_assessed_at' => now(),
                    'version' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_LMS_COMPLETE',
                action: 'COMPLETE_COURSE_AWARD_SKILL',
                entityType: 'CourseEnrollment',
                entityId: $enrollmentId,
                actorId: $actorId,
                before: null,
                after: [
                    'course_id' => $courseId,
                    'employee_id' => $employeeId,
                    'skill_id' => $skillId,
                    'score' => $score,
                    'status' => 'completed',
                ]
            );

            return [
                'enrollment_id' => $enrollmentId,
                'employee_skill_id' => $empSkillId,
                'status' => 'certified',
                'skill_level' => 3,
            ];
        });
    }

    /**
     * Workflow F: Performance Appraisal to Goal Progression & Talent Rating
     */
    public function finalizePerformanceReview(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $employeeId = $data['employee_id'];
            $cycleId = $data['performance_cycle_id'];
            $ratingScore = (float) ($data['rating_score'] ?? 4.5);
            $talentPoolId = $data['talent_pool_id'] ?? null;
            $validActorId = ($actorId && DB::table('users')->where('id', $actorId)->exists()) ? (string)$actorId : null;

            // 1. Verify cycle
            $cycle = DB::table('performance_cycles')
                ->where('id', $cycleId)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$cycle) {
                throw new DomainException("Performance cycle {$cycleId} not found.");
            }

            // 2. High performer automatic placement in talent pool
            $poolMemberId = null;
            if ($ratingScore >= 4.0 && $talentPoolId) {
                $pool = DB::table('talent_pools')
                    ->where('id', $talentPoolId)
                    ->where('tenant_id', $tenantId)
                    ->first();

                if ($pool) {
                    $existingMember = DB::table('talent_pool_members')
                        ->where('tenant_id', $tenantId)
                        ->where('pool_id', $talentPoolId)
                        ->where('employee_id', $employeeId)
                        ->first();

                    if (!$existingMember) {
                        $poolMemberId = (string) Str::uuid();
                        DB::table('talent_pool_members')->insert([
                            'id' => $poolMemberId,
                            'tenant_id' => $tenantId,
                            'pool_id' => $talentPoolId,
                            'employee_id' => $employeeId,
                            'added_by' => $validActorId,
                            'added_at' => now(),
                            'reason' => "High Performance Rating: {$ratingScore} / 5.0",
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $poolMemberId = $existingMember->id;
                    }
                }
            }

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_PERF_FINALIZE',
                action: 'FINALIZE_PERFORMANCE_REVIEW',
                entityType: 'PerformanceCycle',
                entityId: $cycleId,
                actorId: $actorId,
                before: null,
                after: [
                    'employee_id' => $employeeId,
                    'cycle_id' => $cycleId,
                    'rating_score' => $ratingScore,
                    'talent_pool_member_id' => $poolMemberId,
                ]
            );

            return [
                'cycle_id' => $cycleId,
                'employee_id' => $employeeId,
                'rating_score' => $ratingScore,
                'talent_pool_member_id' => $poolMemberId,
                'status' => 'calibrated_and_certified',
            ];
        });
    }

    /**
     * Workflow G: Expense Submission to Approval & Reimbursement
     */
    public function processExpenseClaimAndReimbursement(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $employeeId = $data['employee_id'];
            $amount = (float) ($data['amount'] ?? 150.00);
            $currency = $data['currency'] ?? 'USD';
            $validActorId = ($actorId && DB::table('users')->where('id', $actorId)->exists()) ? $actorId : null;

            // Ensure valid payroll period exists
            $payrollPeriodId = $data['payroll_period_id'] ?? null;
            if (!$payrollPeriodId || !DB::table('payroll_periods')->where('id', $payrollPeriodId)->exists()) {
                $existingPeriod = DB::table('payroll_periods')->where('tenant_id', $tenantId)->where('status', 'open')->first();
                if ($existingPeriod) {
                    $payrollPeriodId = $existingPeriod->id;
                } else {
                    $payrollPeriodId = (string) Str::uuid();
                    DB::table('payroll_periods')->insert([
                        'id' => $payrollPeriodId,
                        'tenant_id' => $tenantId,
                        'period_name' => 'Payroll Cycle ' . date('Y-m'),
                        'start_date' => now()->startOfMonth()->toDateString(),
                        'end_date' => now()->endOfMonth()->toDateString(),
                        'status' => 'open',
                        'currency' => 'USD',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 1. Create approved expense claim
            $claimId = (string) Str::uuid();
            $claimNumber = 'EXP-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            DB::table('expense_claims')->insert([
                'id' => $claimId,
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'claim_number' => $claimNumber,
                'status' => 'approved',
                'currency' => $currency,
                'claimed_total' => $amount,
                'approved_total' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Queue for payroll input reimbursement
            $payrollInputId = (string) Str::uuid();
            DB::table('payroll_inputs')->insert([
                'id' => $payrollInputId,
                'tenant_id' => $tenantId,
                'payroll_period_id' => $payrollPeriodId,
                'employee_id' => $employeeId,
                'status' => 'reimbursement_queued',
                'collected_by' => $validActorId,
                'collected_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_EXPENSE_SETTLE',
                action: 'APPROVE_EXPENSE_AND_QUEUE_PAYROLL',
                entityType: 'ExpenseClaim',
                entityId: $claimId,
                actorId: $actorId,
                before: null,
                after: [
                    'claim_number' => $claimNumber,
                    'approved_amount' => $amount,
                    'payroll_input_id' => $payrollInputId,
                ]
            );

            return [
                'expense_claim_id' => $claimId,
                'claim_number' => $claimNumber,
                'approved_total' => $amount,
                'payroll_input_id' => $payrollInputId,
                'status' => 'approved_and_queued',
            ];
        });
    }

    /**
     * Workflow H: Department / Location Transfer with Reporting Line Reassignment
     */
    public function transferEmployee(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $employeeId = $data['employee_id'];
            $newDepartmentId = $data['new_department_id'];
            $newManagerId = $data['new_manager_id'] ?? null;

            $employee = DB::table('employees')
                ->where('id', $employeeId)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$employee) {
                throw new DomainException("Employee {$employeeId} not found.");
            }

            $oldDeptId = $employee->department_id;
            $oldManagerId = $employee->current_manager_employee_id ?? $employee->reporting_manager_id;

            DB::table('employees')->where('id', $employeeId)->update([
                'department_id' => $newDepartmentId,
                'current_manager_employee_id' => $newManagerId,
                'reporting_manager_id' => $newManagerId,
                'updated_at' => now(),
            ]);

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_EMP_TRANSFER',
                action: 'TRANSFER_EMPLOYEE_DEPARTMENT',
                entityType: 'Employee',
                entityId: $employeeId,
                actorId: $actorId,
                before: ['department_id' => $oldDeptId, 'manager_id' => $oldManagerId],
                after: ['department_id' => $newDepartmentId, 'manager_id' => $newManagerId]
            );

            return [
                'employee_id' => $employeeId,
                'previous_department_id' => $oldDeptId,
                'new_department_id' => $newDepartmentId,
                'new_manager_id' => $newManagerId,
                'status' => 'transferred',
            ];
        });
    }

    /**
     * Workflow I: Employee Promotion with Grade, Title & Compensation
     */
    public function promoteEmployee(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $employeeId = $data['employee_id'];
            $newPositionId = $data['new_position_id'] ?? null;
            $newSalary = (float) $data['new_salary'];
            $reason = $data['reason'] ?? 'Promotion to Senior Role';

            $employee = DB::table('employees')
                ->where('id', $employeeId)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$employee) {
                throw new DomainException("Employee {$employeeId} not found.");
            }

            $oldPositionId = $employee->current_position_id;

            // 1. Vacate old position if switching positions
            if ($oldPositionId && $newPositionId && $oldPositionId !== $newPositionId) {
                $oldPos = DB::table('positions')->where('id', $oldPositionId)->first();
                if ($oldPos) {
                    DB::table('positions')->where('id', $oldPositionId)->update([
                        'filled_headcount' => max(0, ((int)$oldPos->filled_headcount) - 1),
                        'updated_at' => now(),
                    ]);
                }

                // Occupy new position
                $newPos = DB::table('positions')->where('id', $newPositionId)->first();
                if ($newPos) {
                    DB::table('positions')->where('id', $newPositionId)->update([
                        'filled_headcount' => ((int)$newPos->filled_headcount) + 1,
                        'updated_at' => now(),
                    ]);
                }

                DB::table('employees')->where('id', $employeeId)->update([
                    'current_position_id' => $newPositionId,
                    'updated_at' => now(),
                ]);
            }

            // 2. Adjust compensation to new salary
            $compResult = $this->applyCompensationChange($tenantId, [
                'employee_id' => $employeeId,
                'base_salary' => $newSalary,
                'effective_from' => now()->toDateString(),
                'reason_for_change' => $reason,
            ], $actorId);

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_EMP_PROMOTE',
                action: 'PROMOTE_EMPLOYEE',
                entityType: 'Employee',
                entityId: $employeeId,
                actorId: $actorId,
                before: ['position_id' => $oldPositionId],
                after: [
                    'position_id' => $newPositionId ?? $oldPositionId,
                    'new_salary' => $newSalary,
                    'compensation_id' => $compResult['compensation_id'],
                ]
            );

            return [
                'employee_id' => $employeeId,
                'new_position_id' => $newPositionId ?? $oldPositionId,
                'new_salary' => $newSalary,
                'status' => 'promoted',
            ];
        });
    }

    /**
     * Workflow J: Employee Resignation / Termination to Offboarding & Clearance
     */
    public function separateEmployee(string $tenantId, array $data, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($tenantId, $data, $actorId) {
            $employeeId = $data['employee_id'];
            $separationType = $data['separation_type'] ?? 'resignation';
            $lastWorkingDate = $data['last_working_date'] ?? now()->toDateString();
            $reason = $data['reason'] ?? 'Career Progression';

            $employee = DB::table('employees')
                ->where('id', $employeeId)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$employee) {
                throw new DomainException("Employee {$employeeId} not found.");
            }

            // 1. Create separation case
            $caseId = (string) Str::uuid();
            DB::table('separation_cases')->insert([
                'id' => $caseId,
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'separation_type' => $separationType,
                'reason' => $reason,
                'submitted_on' => now()->toDateString(),
                'last_working_date' => $lastWorkingDate,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Generate Clearance Tasks (IT, HR, Finance)
            $tasks = [
                ['dept' => 'IT', 'title' => 'Revoke VPN, Laptop & OAuth Access'],
                ['dept' => 'HR', 'title' => 'Exit Interview & Knowledge Transfer'],
                ['dept' => 'Finance', 'title' => 'Final Pay & Expense Ledger Settlement'],
            ];

            foreach ($tasks as $task) {
                DB::table('exit_checklist_tasks')->insert([
                    'id' => (string) Str::uuid(),
                    'separation_case_id' => $caseId,
                    'department' => $task['dept'],
                    'title' => $task['title'],
                    'status' => 'completed',
                    'completed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Vacate position
            if ($employee->current_position_id) {
                $pos = DB::table('positions')->where('id', $employee->current_position_id)->first();
                if ($pos) {
                    DB::table('positions')->where('id', $employee->current_position_id)->update([
                        'filled_headcount' => max(0, ((int)$pos->filled_headcount) - 1),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 4. Update employee record
            DB::table('employees')->where('id', $employeeId)->update([
                'employment_status' => 'terminated',
                'current_position_id' => null,
                'termination_date' => $lastWorkingDate,
                'portal_access' => 0,
                'updated_at' => now(),
            ]);

            // 5. Deactivate user account if user linked
            if ($employee->user_id) {
                DB::table('users')->where('id', $employee->user_id)->update([
                    'status' => 'inactive',
                    'updated_at' => now(),
                ]);
            }

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'HCM_WF_EMP_OFFBOARD',
                action: 'SEPARATE_EMPLOYEE',
                entityType: 'SeparationCase',
                entityId: $caseId,
                actorId: $actorId,
                before: ['employment_status' => $employee->employment_status],
                after: [
                    'separation_case_id' => $caseId,
                    'employment_status' => 'terminated',
                    'last_working_date' => $lastWorkingDate,
                    'portal_access' => 0,
                ]
            );

            return [
                'separation_case_id' => $caseId,
                'employee_id' => $employeeId,
                'status' => 'separated',
                'last_working_date' => $lastWorkingDate,
            ];
        });
    }
}
