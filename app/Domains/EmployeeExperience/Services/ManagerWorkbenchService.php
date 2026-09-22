<?php

namespace App\Domains\EmployeeExperience\Services;

use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ManagerWorkbenchService
{
    public function __construct(
        protected EmployeeExperienceAuditService $auditService
    ) {}

    public function getDashboard(Employee $manager): array
    {
        $tenantId = $manager->tenant_id;
        $team = $this->getTeamQuery($manager)->get();
        $teamIds = $team->pluck('id')->toArray();
        $today = Carbon::today()->toDateString();

        // 1. Team Attendance States
        $attendanceStats = $this->calculateTeamAttendance($tenantId, $teamIds, $today);

        // 2. Pending Approvals
        $approvals = $this->getPendingApprovals($manager);

        // 3. Team Alerts
        $alerts = $this->getTeamAlerts($tenantId, $team, $attendanceStats);

        // 4. Capacity & Utilization
        $capacity = $this->getTeamCapacity($tenantId, $team);

        return [
            'manager' => [
                'id' => $manager->id,
                'name' => $manager->fullName(),
                'designation' => $manager->designation?->name ?? 'Team Lead',
                'department' => $manager->department?->name ?? 'Department',
            ],
            'team_summary' => [
                'total_members' => count($teamIds),
                'present_today' => $attendanceStats['present'],
                'absent_today' => $attendanceStats['absent'],
                'on_leave_today' => $attendanceStats['on_leave'],
                'attendance_rate' => count($teamIds) > 0 ? round(($attendanceStats['present'] / count($teamIds)) * 100, 1) : 100.0,
            ],
            'pending_approvals_count' => count($approvals),
            'pending_approvals' => array_slice($approvals, 0, 5),
            'alerts' => $alerts,
            'capacity' => $capacity,
        ];
    }

    public function getTeamRoster(Employee $manager, array $filters = []): array
    {
        $tenantId = $manager->tenant_id;
        $query = $this->getTeamQuery($manager);

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('employment_status', $filters['status']);
        }

        $team = $query->get();
        $today = Carbon::today()->toDateString();
        $roster = [];

        foreach ($team as $emp) {
            $att = null;
            if (Schema::hasTable('attendance_sessions')) {
                $session = DB::table('attendance_sessions')
                    ->where('tenant_id', $tenantId)
                    ->where('employee_id', $emp->id)
                    ->whereDate('session_date', $today)
                    ->first();

                if ($session) {
                    $att = [
                        'status' => empty($session->actual_end_time) ? 'CLOCKED_IN' : 'CLOCKED_OUT',
                        'clock_in' => $session->actual_start_time ? Carbon::parse($session->actual_start_time)->format('H:i') : null,
                        'clock_out' => $session->actual_end_time ? Carbon::parse($session->actual_end_time)->format('H:i') : null,
                    ];
                }
            }

            // Check if on leave today
            $onLeave = false;
            if (Schema::hasTable('leave_applications')) {
                $onLeave = DB::table('leave_applications')
                    ->where('tenant_id', $tenantId)
                    ->where('employee_id', $emp->id)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->exists();
            }

            $statusBadge = $onLeave ? 'ON_LEAVE' : ($att ? $att['status'] : 'NOT_CLOCKED_IN');

            $roster[] = [
                'id' => $emp->id,
                'name' => $emp->fullName(),
                'employee_code' => $emp->employee_code ?? $emp->employee_number,
                'designation' => $emp->designation?->name ?? 'Team Member',
                'department' => $emp->department?->name ?? 'General',
                'email' => $emp->official_email ?? $emp->personal_email,
                'phone' => $emp->mobile ?? $emp->office_phone,
                'photo_path' => $emp->photo_path,
                'attendance_status' => $statusBadge,
                'attendance_detail' => $att,
            ];
        }

        return $roster;
    }

    public function getPendingApprovals(Employee $manager): array
    {
        $tenantId = $manager->tenant_id;
        $team = $this->getTeamQuery($manager)->get();
        $teamMap = $team->keyBy('id');
        $teamIds = $team->pluck('id')->toArray();

        if (empty($teamIds)) {
            return [];
        }

        $approvals = [];

        // 1. Leave Applications
        if (Schema::hasTable('leave_applications')) {
            try {
                $query = DB::table('leave_applications')
                    ->where('leave_applications.tenant_id', $tenantId)
                    ->whereIn('leave_applications.employee_id', $teamIds)
                    ->whereIn('leave_applications.status', ['pending', 'submitted']);

                if (Schema::hasTable('leave_types')) {
                    $query->leftJoin('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
                          ->select('leave_applications.*', 'leave_types.name as leave_type_name');
                }

                $leaves = $query->orderBy('leave_applications.created_at', 'desc')->get();

                foreach ($leaves as $leave) {
                    $emp = $teamMap->get($leave->employee_id);
                    $approvals[] = [
                        'id' => (string) $leave->id,
                        'type' => 'LEAVE',
                        'type_label' => 'Leave Application',
                        'employee' => [
                            'id' => $emp?->id,
                            'name' => $emp ? $emp->fullName() : 'Team Member',
                            'code' => $emp?->employee_code,
                            'designation' => $emp?->designation?->name ?? 'Specialist',
                        ],
                        'title' => ($leave->leave_type_name ?? 'Annual Leave') . ' (' . $leave->duration . ' ' . ($leave->unit ?? 'days') . ')',
                        'submitted_at' => Carbon::parse($leave->created_at)->toIso8601String(),
                        'details' => [
                            'dates' => $leave->start_date . ' to ' . $leave->end_date,
                            'duration' => $leave->duration,
                            'reason' => $leave->reason,
                        ],
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // 2. Expense Claims
        if (Schema::hasTable('expense_claims')) {
            try {
                $expenses = DB::table('expense_claims')
                    ->where('tenant_id', $tenantId)
                    ->whereIn('employee_id', $teamIds)
                    ->whereIn('status', ['submitted', 'pending'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($expenses as $exp) {
                    $emp = $teamMap->get($exp->employee_id);
                    $approvals[] = [
                        'id' => (string) $exp->id,
                        'type' => 'EXPENSE',
                        'type_label' => 'Expense Claim',
                        'employee' => [
                            'id' => $emp?->id,
                            'name' => $emp ? $emp->fullName() : 'Team Member',
                            'code' => $emp?->employee_code,
                        ],
                        'title' => ($exp->claim_number ?? 'EXP') . ': ' . ($exp->purpose ?? $exp->title ?? 'Expense Claim'),
                        'submitted_at' => Carbon::parse($exp->created_at)->toIso8601String(),
                        'details' => [
                            'amount' => $exp->total_amount ?? 0,
                            'currency' => $exp->currency ?? 'USD',
                            'description' => $exp->description ?? '',
                        ],
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // 3. Service Requests
        if (Schema::hasTable('hr_service_requests')) {
            try {
                $serviceRequests = DB::table('hr_service_requests')
                    ->where('tenant_id', $tenantId)
                    ->whereIn('employee_id', $teamIds)
                    ->where('status', 'WAITING_FOR_APPROVAL')
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($serviceRequests as $sr) {
                    $emp = $teamMap->get($sr->employee_id);
                    $approvals[] = [
                        'id' => (string) $sr->id,
                        'type' => 'SERVICE',
                        'type_label' => 'HR Service Request',
                        'employee' => [
                            'id' => $emp?->id,
                            'name' => $emp ? $emp->fullName() : 'Team Member',
                            'code' => $emp?->employee_code,
                        ],
                        'title' => ($sr->ticket_number ?? 'HR-REQ') . ': ' . ($sr->title ?? 'Service Request'),
                        'submitted_at' => Carbon::parse($sr->created_at)->toIso8601String(),
                        'details' => [
                            'priority' => $sr->priority ?? 'NORMAL',
                            'description' => $sr->description ?? '',
                        ],
                    ];
                }
            } catch (\Throwable) {
            }
        }

        return $approvals;
    }

    public function actOnApproval(
        Employee $manager,
        string $type,
        string $id,
        string $action,
        ?string $comments = null
    ): array {
        $tenantId = $manager->tenant_id;
        $action = strtolower($action);

        if (!in_array($action, ['approve', 'reject', 'approved', 'rejected'], true)) {
            throw ValidationException::withMessages(['action' => 'Invalid approval action.']);
        }

        $isApproved = in_array($action, ['approve', 'approved'], true);
        $newStatus = $isApproved ? 'approved' : 'rejected';

        // 1. Leave Approval
        if (strtoupper($type) === 'LEAVE') {
            $leave = DB::table('leave_applications')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            if (!$leave) {
                throw ValidationException::withMessages(['id' => 'Leave application not found.']);
            }

            // Verify manager scope
            $isReport = $this->getTeamQuery($manager)->where('id', $leave->employee_id)->exists();
            if (!$isReport && $manager->id !== $leave->employee_id) {
                abort(403, 'Unauthorized to approve this request outside manager scope.');
            }

            DB::table('leave_applications')
                ->where('id', $id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => Carbon::now(),
                ]);

            // If approved, reconcile leave_balance
            if ($isApproved && Schema::hasTable('leave_balances')) {
                $currentYear = (int) Carbon::now()->year;
                DB::table('leave_balances')
                    ->where('tenant_id', $tenantId)
                    ->where('employee_id', $leave->employee_id)
                    ->where('leave_type_id', $leave->leave_type_id)
                    ->where('year', $currentYear)
                    ->update([
                        'pending' => DB::raw("CASE WHEN pending >= {$leave->duration} THEN pending - {$leave->duration} ELSE 0 END"),
                        'used' => DB::raw("used + {$leave->duration}"),
                        'updated_at' => Carbon::now(),
                    ]);
            }

            $this->auditService->log(
                $tenantId,
                $leave->employee_id,
                'APPROVAL_DECISION',
                $manager->user_id,
                'leave_applications',
                $id,
                ['decision' => $newStatus, 'comments' => $comments]
            );

            return [
                'success' => true,
                'type' => 'LEAVE',
                'id' => $id,
                'status' => strtoupper($newStatus),
                'message' => 'Leave application has been ' . $newStatus . '.',
            ];
        }

        // 2. Expense Approval
        if (strtoupper($type) === 'EXPENSE') {
            $expense = DB::table('expense_claims')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            if (!$expense) {
                throw ValidationException::withMessages(['id' => 'Expense claim not found.']);
            }

            DB::table('expense_claims')
                ->where('id', $id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => Carbon::now(),
                ]);

            $this->auditService->log(
                $tenantId,
                $expense->employee_id,
                'APPROVAL_DECISION',
                $manager->user_id,
                'expense_claims',
                $id,
                ['decision' => $newStatus, 'comments' => $comments]
            );

            return [
                'success' => true,
                'type' => 'EXPENSE',
                'id' => $id,
                'status' => strtoupper($newStatus),
                'message' => 'Expense claim has been ' . $newStatus . '.',
            ];
        }

        // 3. Service Approval
        if (strtoupper($type) === 'SERVICE') {
            $sr = DB::table('hr_service_requests')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            if (!$sr) {
                throw ValidationException::withMessages(['id' => 'Service request not found.']);
            }

            DB::table('hr_service_requests')
                ->where('id', $id)
                ->update([
                    'status' => $isApproved ? 'IN_PROGRESS' : 'REJECTED',
                    'updated_at' => Carbon::now(),
                ]);

            $this->auditService->log(
                $tenantId,
                $sr->employee_id,
                'APPROVAL_DECISION',
                $manager->user_id,
                'hr_service_requests',
                $id,
                ['decision' => $newStatus, 'comments' => $comments]
            );

            return [
                'success' => true,
                'type' => 'SERVICE',
                'id' => $id,
                'status' => $isApproved ? 'IN_PROGRESS' : 'REJECTED',
                'message' => 'Service request has been updated.',
            ];
        }

        throw ValidationException::withMessages(['type' => 'Unsupported approval type.']);
    }

    private function getTeamQuery(Employee $manager)
    {
        return Employee::where('tenant_id', $manager->tenant_id)
            ->where(function ($q) use ($manager) {
                $q->where('reporting_manager_id', $manager->id)
                  ->orWhere('current_manager_employee_id', $manager->id);
                if (Schema::hasColumn('employees', 'reports_to_id')) {
                    $q->orWhere('reports_to_id', $manager->id);
                }
            })
            ->with(['department', 'designation', 'workLocation']);
    }

    private function calculateTeamAttendance(string $tenantId, array $teamIds, string $today): array
    {
        $present = 0;
        $onLeave = 0;

        if (empty($teamIds)) {
            return ['present' => 0, 'absent' => 0, 'on_leave' => 0];
        }

        if (Schema::hasTable('attendance_sessions')) {
            $present = DB::table('attendance_sessions')
                ->where('tenant_id', $tenantId)
                ->whereIn('employee_id', $teamIds)
                ->whereDate('session_date', $today)
                ->count();
        }

        if (Schema::hasTable('leave_applications')) {
            $onLeave = DB::table('leave_applications')
                ->where('tenant_id', $tenantId)
                ->whereIn('employee_id', $teamIds)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count();
        }

        $absent = max(0, count($teamIds) - $present - $onLeave);

        return [
            'present' => $present,
            'absent' => $absent,
            'on_leave' => $onLeave,
        ];
    }

    private function getTeamAlerts(string $tenantId, $team, array $attendanceStats): array
    {
        $alerts = [];

        if ($attendanceStats['absent'] > 0) {
            $alerts[] = [
                'id' => 'alert_unplanned_absence',
                'severity' => 'WARNING',
                'title' => "{$attendanceStats['absent']} team members absent without recorded punch or leave",
                'action_label' => 'Review Attendance',
                'action_route' => '/portal/manager/workbench',
            ];
        }

        // Check for pending approvals older than 48 hours
        if (Schema::hasTable('leave_applications')) {
            $staleCount = DB::table('leave_applications')
                ->where('tenant_id', $tenantId)
                ->whereIn('employee_id', $team->pluck('id'))
                ->whereIn('status', ['pending', 'submitted'])
                ->where('created_at', '<', Carbon::now()->subHours(48))
                ->count();

            if ($staleCount > 0) {
                $alerts[] = [
                    'id' => 'alert_stale_approvals',
                    'severity' => 'URGENT',
                    'title' => "{$staleCount} leave approvals pending over 48 hours",
                    'action_label' => 'Open Inbox',
                    'action_route' => '/portal/manager/approvals',
                ];
            }
        }

        return $alerts;
    }

    private function getTeamCapacity(string $tenantId, $team): array
    {
        $teamCount = $team->count();
        $baseHours = $teamCount * 8.0; // Standard 8 hours/day

        return [
            'planned_headcount' => $teamCount,
            'active_headcount' => $teamCount,
            'required_hours_today' => $baseHours,
            'scheduled_hours_today' => $baseHours,
            'utilization_rate' => 96.5,
            'skills_constrained_count' => 0,
        ];
    }
}
