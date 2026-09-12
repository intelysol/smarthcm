<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollCalculationSnapshot;
use App\Domains\Payroll\Models\PayrollPayslip;
use App\Domains\Payroll\Models\PayrollRun;
use App\Models\User;

class PayrollAuthorizationService
{
    public function canViewPayroll(User $user, ?string $tenantId = null): bool
    {
        if ($tenantId && $user->tenant_id !== $tenantId) {
            return false;
        }

        return $user->hasPermission('hcm.payroll.view') || $user->hasPermission('hcm.payroll.manage');
    }

    public function canManagePayroll(User $user, ?string $tenantId = null): bool
    {
        if ($tenantId && $user->tenant_id !== $tenantId) {
            return false;
        }

        return $user->hasPermission('hcm.payroll.manage');
    }

    public function canViewSalary(User $user, Employee $employee): bool
    {
        if ($user->tenant_id !== $employee->tenant_id) {
            return false;
        }

        // Employee viewing their own compensation
        if ($employee->user_id && (int) $employee->user_id === (int) $user->id) {
            return true;
        }

        // HR / Payroll specialist with explicit salary permission
        return $user->hasPermission('hcm.payroll.salary.view') || $user->hasPermission('hcm.payroll.manage');
    }

    public function canViewPayslip(User $user, PayrollPayslip $payslip): bool
    {
        if ($user->tenant_id !== $payslip->tenant_id) {
            return false;
        }

        // Employee viewing own published payslip
        $emp = $payslip->employee;
        if ($emp && $emp->user_id && (int) $emp->user_id === (int) $user->id) {
            return (bool) $payslip->is_published;
        }

        // HR / Payroll Manager with explicit payslip permission
        return $user->hasPermission('hcm.payroll.payslip.view') || $user->hasPermission('hcm.payroll.manage');
    }

    public function canExportAccounting(User $user, PayrollRun $run): bool
    {
        if ($user->tenant_id !== $run->tenant_id) {
            return false;
        }

        return $user->hasPermission('hcm.payroll.accounting.export') || $user->hasPermission('hcm.payroll.manage');
    }
}
