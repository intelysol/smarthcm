<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceRequest;
use Illuminate\Database\Eloquent\Collection;

class ManagerPortalService
{
    public function getManagerDashboard(Employee $manager): array
    {
        $tenantId = $manager->tenant_id;
        $team = Employee::where('tenant_id', $tenantId)
            ->where(function ($q) use ($manager) {
                $q->where('reporting_manager_id', $manager->id)
                  ->orWhere('current_manager_employee_id', $manager->id);
            })
            ->with(['designation', 'department'])
            ->get();

        $teamIds = $team->pluck('id')->toArray();

        $teamRequests = HrServiceRequest::where('tenant_id', $tenantId)
            ->whereIn('employee_id', $teamIds)
            ->with(['employee', 'service'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingApprovalsCount = HrServiceRequest::where('tenant_id', $tenantId)
            ->where('reporting_manager_id', $manager->id)
            ->where('status', ServiceRequestStatus::WAITING_FOR_APPROVAL->value)
            ->count();

        // Pending Expense Approvals for Team
        $pendingExpenseApprovals = ExpenseClaim::where('tenant_id', $tenantId)
            ->whereIn('employee_id', $teamIds)
            ->where('status', 'submitted')
            ->count();

        return [
            'manager' => $manager,
            'team_size' => $team->count(),
            'team_roster' => $team,
            'team_requests' => $teamRequests,
            'pending_service_approvals_count' => $pendingApprovalsCount,
            'pending_expense_approvals_count' => $pendingExpenseApprovals,
            'total_pending_approvals' => $pendingApprovalsCount + $pendingExpenseApprovals,
        ];
    }
}
