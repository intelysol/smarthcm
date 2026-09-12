<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Enums\ExceptionSeverity;
use App\Domains\Payroll\Models\PayrollException;
use App\Domains\Payroll\Models\PayrollRun;

class PayrollValidationService
{
    /**
     * Inspect calculation snapshots and raise blocking/warning exceptions.
     */
    public function validateRun(PayrollRun $run): array
    {
        $tenantId = $run->tenant_id;
        $run->loadMissing(['calculationSnapshots.employee']);

        $exceptions = [];

        // Clear existing unresolved exceptions for this run
        $run->exceptions()->where('is_resolved', false)->delete();

        foreach ($run->calculationSnapshots as $snapshot) {
            $emp = $snapshot->employee;
            $netPay = (float) $snapshot->net_pay;
            $grossPay = (float) $snapshot->gross_pay;

            // 1. Negative Net Pay Check (Blocking)
            if ($netPay < 0) {
                $exceptions[] = PayrollException::query()->create([
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $snapshot->employee_id,
                    'exception_type' => 'negative_net',
                    'severity' => ExceptionSeverity::BLOCKING->value,
                    'message' => "Negative net salary of \${$netPay} calculated for {$emp->fullName()}.",
                ]);
            }

            // 2. Missing/Zero Gross Salary Check (Warning)
            if ($grossPay <= 0) {
                $exceptions[] = PayrollException::query()->create([
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $snapshot->employee_id,
                    'exception_type' => 'missing_salary',
                    'severity' => ExceptionSeverity::WARNING->value,
                    'message' => "Zero gross salary calculated for active employee {$emp->fullName()}.",
                ]);
            }

            // 3. Inactive Employee Check (Warning)
            if ($emp->employment_status && $emp->employment_status !== 'active') {
                $exceptions[] = PayrollException::query()->create([
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $snapshot->employee_id,
                    'exception_type' => 'inactive_employee',
                    'severity' => ExceptionSeverity::WARNING->value,
                    'message' => "Payroll calculated for employee with status '{$emp->employment_status}'.",
                ]);
            }
        }

        return $exceptions;
    }
}
