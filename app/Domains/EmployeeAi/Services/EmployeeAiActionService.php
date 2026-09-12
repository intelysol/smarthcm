<?php

namespace App\Domains\EmployeeAi\Services;

use App\Domains\EmployeeAi\Models\HcmAiConciergeAction;
use Carbon\Carbon;

class EmployeeAiActionService
{
    public function prepareLeaveRequestAction(string $tenantId, string $userId, string $employeeId, array $dates, string $leaveType = 'ANNUAL'): HcmAiConciergeAction
    {
        $startDate = $dates['start_date'] ?? Carbon::now()->addDays(2)->toDateString();
        $endDate = $dates['end_date'] ?? Carbon::now()->addDays(5)->toDateString();
        $daysCount = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

        $availableBalance = 14.5;
        $remainingBalance = $availableBalance - $daysCount;

        return HcmAiConciergeAction::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'employee_id' => $employeeId,
            'action_type' => 'SUBMIT_LEAVE_REQUEST',
            'risk_level' => 'MEDIUM',
            'parameters' => [
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $daysCount,
            ],
            'preview_data' => [
                'summary' => "Request {$daysCount} days of {$leaveType} Leave from {$startDate} to {$endDate}",
                'available_balance' => $availableBalance,
                'requested_days' => $daysCount,
                'projected_remaining_balance' => $remainingBalance,
                'approver_role' => 'Line Manager',
                'policy_validation' => 'ELIGIBLE',
            ],
            'status' => 'PROPOSED',
        ]);
    }

    public function confirmAction(string $actionId, string $userId, ?string $comment = null): HcmAiConciergeAction
    {
        $action = HcmAiConciergeAction::findOrFail($actionId);
        $action->update([
            'status' => 'SUBMITTED',
            'user_comment' => $comment,
            'confirmed_at' => Carbon::now(),
            'workflow_reference_id' => 'WF-ACT-' . strtoupper(bin2hex(random_bytes(4))),
        ]);

        return $action;
    }
}
