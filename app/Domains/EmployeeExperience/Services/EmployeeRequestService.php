<?php

namespace App\Domains\EmployeeExperience\Services;

use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmployeeRequestService
{
    public function getUnifiedRequests(Employee $employee, ?string $statusFilter = null): array
    {
        $tenantId = $employee->tenant_id;
        $employeeId = $employee->id;
        $requests = [];

        // 1. Leave Applications
        if (Schema::hasTable('leave_applications')) {
            try {
                $query = DB::table('leave_applications')
                    ->where('leave_applications.tenant_id', $tenantId)
                    ->where('leave_applications.employee_id', $employeeId);

                if (Schema::hasTable('leave_types')) {
                    $query->leftJoin('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
                          ->select('leave_applications.*', 'leave_types.name as leave_type_name');
                }

                $leaves = $query->orderBy('leave_applications.created_at', 'desc')->get();

                foreach ($leaves as $leave) {
                    $requests[] = [
                        'id' => (string) $leave->id,
                        'type' => 'LEAVE',
                        'type_label' => 'Leave Request',
                        'title' => ($leave->leave_type_name ?? 'Leave') . ' (' . $leave->duration . ' ' . ($leave->unit ?? 'days') . ')',
                        'status' => strtoupper($leave->status ?? 'SUBMITTED'),
                        'submitted_at' => Carbon::parse($leave->created_at)->toDateTimeString(),
                        'current_step' => match (strtolower($leave->status ?? '')) {
                            'pending', 'submitted' => 'Manager Approval',
                            'approved' => 'Approved & Processed',
                            'rejected' => 'Rejected',
                            default => 'Draft',
                        },
                        'next_action' => match (strtolower($leave->status ?? '')) {
                            'pending', 'submitted' => 'Awaiting Manager Review',
                            'approved' => 'None (Completed)',
                            default => 'Pending submission',
                        },
                        'approver' => $employee->reportingManager ? $employee->reportingManager->fullName() : 'Reporting Manager',
                        'timeline' => [
                            ['step' => 'Submitted', 'completed' => true, 'timestamp' => Carbon::parse($leave->created_at)->toDateString()],
                            ['step' => 'Manager Approval', 'completed' => in_array(strtolower($leave->status ?? ''), ['approved', 'rejected']), 'current' => in_array(strtolower($leave->status ?? ''), ['pending', 'submitted'])],
                            ['step' => 'Completed', 'completed' => strtolower($leave->status ?? '') === 'approved', 'current' => false],
                        ],
                        'source_module' => 'LEAVE',
                        'details' => [
                            'start_date' => $leave->start_date,
                            'end_date' => $leave->end_date,
                            'reason' => $leave->reason,
                            'duration' => $leave->duration,
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
                    ->where('employee_id', $employeeId)
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($expenses as $exp) {
                    $requests[] = [
                        'id' => (string) $exp->id,
                        'type' => 'EXPENSE',
                        'type_label' => 'Expense Claim',
                        'title' => ($exp->claim_number ?? 'EXP') . ': ' . ($exp->purpose ?? $exp->title ?? 'Expense Claim'),
                        'status' => strtoupper($exp->status ?? 'SUBMITTED'),
                        'submitted_at' => Carbon::parse($exp->created_at)->toDateTimeString(),
                        'current_step' => match (strtolower($exp->status ?? '')) {
                            'pending', 'submitted' => 'Manager Approval',
                            'approved' => 'Finance Settlement',
                            'paid' => 'Reimbursed',
                            'rejected' => 'Rejected',
                            default => 'Draft',
                        },
                        'next_action' => strtolower($exp->status ?? '') === 'submitted' ? 'Manager Review' : 'Processed',
                        'approver' => $employee->reportingManager ? $employee->reportingManager->fullName() : 'Finance / Manager',
                        'timeline' => [
                            ['step' => 'Submitted', 'completed' => true, 'timestamp' => Carbon::parse($exp->created_at)->toDateString()],
                            ['step' => 'Manager Review', 'completed' => in_array(strtolower($exp->status ?? ''), ['approved', 'paid', 'rejected']), 'current' => strtolower($exp->status ?? '') === 'submitted'],
                            ['step' => 'Settlement', 'completed' => strtolower($exp->status ?? '') === 'paid', 'current' => strtolower($exp->status ?? '') === 'approved'],
                        ],
                        'source_module' => 'EXPENSES',
                        'details' => [
                            'amount' => $exp->total_amount ?? 0,
                            'currency' => $exp->currency ?? 'USD',
                            'description' => $exp->description ?? $exp->purpose ?? '',
                        ],
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // 3. HR Service Requests
        if (Schema::hasTable('hr_service_requests')) {
            try {
                $serviceRequests = DB::table('hr_service_requests')
                    ->where('tenant_id', $tenantId)
                    ->where('employee_id', $employeeId)
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($serviceRequests as $sr) {
                    $requests[] = [
                        'id' => (string) $sr->id,
                        'type' => 'SERVICE',
                        'type_label' => 'HR Service Request',
                        'title' => ($sr->ticket_number ?? 'HR-REQ') . ': ' . ($sr->title ?? $sr->subject ?? 'Service Request'),
                        'status' => strtoupper($sr->status ?? 'SUBMITTED'),
                        'submitted_at' => Carbon::parse($sr->created_at)->toDateTimeString(),
                        'current_step' => 'HR Agent Resolution',
                        'next_action' => 'Agent Review & Processing',
                        'approver' => 'HR Shared Services',
                        'timeline' => [
                            ['step' => 'Submitted', 'completed' => true, 'timestamp' => Carbon::parse($sr->created_at)->toDateString()],
                            ['step' => 'In Progress', 'completed' => in_array(strtolower($sr->status ?? ''), ['resolved', 'closed']), 'current' => in_array(strtolower($sr->status ?? ''), ['in_progress', 'assigned'])],
                            ['step' => 'Resolved', 'completed' => in_array(strtolower($sr->status ?? ''), ['resolved', 'closed']), 'current' => false],
                        ],
                        'source_module' => 'HR_SERVICES',
                        'details' => [
                            'ticket_number' => $sr->ticket_number ?? '',
                            'priority' => $sr->priority ?? 'NORMAL',
                            'description' => $sr->description ?? '',
                        ],
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // 4. Profile Change Requests
        if (Schema::hasTable('employee_profile_change_requests')) {
            try {
                $pcr = DB::table('employee_profile_change_requests')
                    ->where('tenant_id', $tenantId)
                    ->where('employee_id', $employeeId)
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($pcr as $change) {
                    $requests[] = [
                        'id' => (string) $change->id,
                        'type' => 'PROFILE_CHANGE',
                        'type_label' => 'Profile Change',
                        'title' => 'Profile Update: ' . ($change->change_category ?? 'Information Update'),
                        'status' => strtoupper($change->status ?? 'PENDING'),
                        'submitted_at' => Carbon::parse($change->created_at)->toDateTimeString(),
                        'current_step' => 'HR Verification',
                        'next_action' => 'HR Verification & Approval',
                        'approver' => 'HR Operations',
                        'timeline' => [
                            ['step' => 'Submitted', 'completed' => true, 'timestamp' => Carbon::parse($change->created_at)->toDateString()],
                            ['step' => 'HR Approval', 'completed' => in_array(strtolower($change->status ?? ''), ['approved', 'rejected']), 'current' => strtolower($change->status ?? '') === 'pending'],
                            ['step' => 'Applied', 'completed' => strtolower($change->status ?? '') === 'approved', 'current' => false],
                        ],
                        'source_module' => 'CORE_HR',
                        'details' => [
                            'notes' => $change->justification ?? $change->notes ?? '',
                        ],
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // Sort by submitted_at desc
        usort($requests, fn ($a, $b) => strcmp($b['submitted_at'], $a['submitted_at']));

        if ($statusFilter && strtolower($statusFilter) !== 'all') {
            $requests = array_values(array_filter($requests, fn ($r) => strtolower($r['status']) === strtolower($statusFilter)));
        }

        return $requests;
    }

    /**
     * Get recent requests for employee workspace overview.
     */
    public function getRecentRequests(Employee $employee, int $limit = 5): array
    {
        $requests = $this->getUnifiedRequests($employee);
        return array_slice($requests, 0, $limit);
    }

    public function submitLeaveApplication(
        Employee $employee,
        string $leaveTypeId,
        string $startDate,
        string $endDate,
        float $duration,
        ?string $reason = null
    ): array {
        $tenantId = $employee->tenant_id;
        $id = (string) Str::uuid();

        // 1. Check or insert into leave_applications
        DB::table('leave_applications')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration' => $duration,
            'unit' => 'day',
            'reason' => $reason,
            'status' => 'pending',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 2. Adjust leave_balance pending count
        if (Schema::hasTable('leave_balances')) {
            $currentYear = (int) Carbon::now()->year;
            DB::table('leave_balances')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', $currentYear)
                ->increment('pending', $duration);
        }

        return [
            'id' => $id,
            'status' => 'PENDING',
            'message' => 'Leave application submitted successfully.',
        ];
    }
}
