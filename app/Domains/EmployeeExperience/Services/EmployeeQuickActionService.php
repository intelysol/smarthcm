<?php

namespace App\Domains\EmployeeExperience\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeExperience\Models\HcmEmployeeQuickAction;

class EmployeeQuickActionService
{
    public function getQuickActionsForEmployee(Employee $employee): array
    {
        $tenantId = $employee->tenant_id;

        if (\Illuminate\Support\Facades\Schema::hasTable('hcm_employee_quick_actions')) {
            $customActions = HcmEmployeeQuickAction::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->orderBy('display_order')
                ->get();

            if ($customActions->isNotEmpty()) {
                return $customActions->map(fn ($action) => [
                    'key' => $action->key,
                    'title' => $action->title,
                    'icon' => $action->icon,
                    'route' => $action->route,
                ])->toArray();
            }
        }

        // Standard Default Actions
        return [
            ['key' => 'apply_leave', 'title' => 'Apply Leave', 'icon' => 'fa-plane-departure', 'route' => '/portal/requests?modal=leave'],
            ['key' => 'clock_toggle', 'title' => 'Clock In / Out', 'icon' => 'fa-clock', 'route' => '/portal/work'],
            ['key' => 'view_payslip', 'title' => 'View Payslip', 'icon' => 'fa-file-invoice-dollar', 'route' => '/portal/pay'],
            ['key' => 'submit_expense', 'title' => 'Submit Expense', 'icon' => 'fa-receipt', 'route' => '/portal/requests?modal=expense'],
            ['key' => 'request_document', 'title' => 'Request Document', 'icon' => 'fa-file-lines', 'route' => '/portal/documents'],
            ['key' => 'view_schedule', 'title' => 'View Schedule', 'icon' => 'fa-calendar-days', 'route' => '/portal/work'],
            ['key' => 'ask_hr', 'title' => 'Ask HR Concierge', 'icon' => 'fa-wand-magic-sparkles', 'route' => '#concierge'],
            ['key' => 'update_profile', 'title' => 'Update Profile', 'icon' => 'fa-user-pen', 'route' => '/portal/profile'],
        ];
    }

    public function getAvailableActions(Employee $employee): array
    {
        return $this->getQuickActionsForEmployee($employee);
    }
}
