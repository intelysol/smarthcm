<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Attendance\Models\AttendancePeriod;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\OvertimeRequest;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Employee\Models\Employee;
use App\Domains\Platform\Services\AuthorizationService;
use App\Models\User;

class AttendanceAuthorizationService
{
    public function __construct(
        protected AuthorizationService $platformAuth
    ) {}

    public function canViewSession(User $user, AttendanceSession $session): bool
    {
        if ($user->tenant_id !== $session->tenant_id) {
            return false;
        }

        // Employee Self-Service
        if ($this->isEmployeeSelf($user, $session->employee_id)) {
            return true;
        }

        // Manager Team Scope
        if ($this->isManagerOfEmployee($user, $session->employee_id)) {
            return true;
        }

        // HR Admin Permission
        return $this->platformAuth->can($user, 'hcm.attendance.view');
    }

    public function canManageAttendance(User $user, string $tenantId): bool
    {
        if ($user->tenant_id !== $tenantId) {
            return false;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.manage');
    }

    public function canViewTimesheet(User $user, Timesheet $timesheet): bool
    {
        if ($user->tenant_id !== $timesheet->tenant_id) {
            return false;
        }

        if ($this->isEmployeeSelf($user, $timesheet->employee_id)) {
            return true;
        }

        if ($this->isManagerOfEmployee($user, $timesheet->employee_id)) {
            return true;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.timesheet.view');
    }

    public function canApproveTimesheet(User $user, Timesheet $timesheet): bool
    {
        if ($user->tenant_id !== $timesheet->tenant_id) {
            return false;
        }

        if ($this->isManagerOfEmployee($user, $timesheet->employee_id)) {
            return true;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.timesheet.approve');
    }

    public function canApproveAdjustment(User $user, AttendanceAdjustment $adjustment): bool
    {
        if ($user->tenant_id !== $adjustment->tenant_id) {
            return false;
        }

        if ($this->isManagerOfEmployee($user, $adjustment->employee_id)) {
            return true;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.approve');
    }

    public function canApproveOvertime(User $user, OvertimeRequest $overtime): bool
    {
        if ($user->tenant_id !== $overtime->tenant_id) {
            return false;
        }

        if ($this->isManagerOfEmployee($user, $overtime->employee_id)) {
            return true;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.overtime.approve');
    }

    public function canManageRoster(User $user, string $tenantId): bool
    {
        if ($user->tenant_id !== $tenantId) {
            return false;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.roster.manage');
    }

    public function canPublishRoster(User $user, string $tenantId): bool
    {
        if ($user->tenant_id !== $tenantId) {
            return false;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.roster.publish');
    }

    public function canLockPeriod(User $user, string $tenantId): bool
    {
        if ($user->tenant_id !== $tenantId) {
            return false;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.period.lock');
    }

    public function canReopenPeriod(User $user, string $tenantId): bool
    {
        if ($user->tenant_id !== $tenantId) {
            return false;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.period.reopen');
    }

    public function canExportPayroll(User $user, string $tenantId): bool
    {
        if ($user->tenant_id !== $tenantId) {
            return false;
        }

        return $this->platformAuth->can($user, 'hcm.attendance.payroll.export');
    }

    protected function isEmployeeSelf(User $user, string $employeeId): bool
    {
        return Employee::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('id', $employeeId)
            ->where('user_id', $user->id)
            ->exists();
    }

    protected function isManagerOfEmployee(User $user, string $employeeId): bool
    {
        $managerEmployee = Employee::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->first();

        if (! $managerEmployee) {
            return false;
        }

        return Employee::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('id', $employeeId)
            ->where('reporting_manager_id', $managerEmployee->id)
            ->exists();
    }
}
