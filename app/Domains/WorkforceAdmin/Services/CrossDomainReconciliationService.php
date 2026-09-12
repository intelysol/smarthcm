<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\WorkforceAdmin\Models\OpsReconciliationResult;
use App\Domains\WorkforceAdmin\Models\OpsReconciliationRule;

class CrossDomainReconciliationService
{
    /**
     * Reconcile Core HR employees with target domains (e.g. Payroll compensation records).
     */
    public function reconcile(OpsReconciliationRule $rule): array
    {
        $tenantId = $rule->tenant_id;
        $results = [];

        // Clear previous results for this rule
        OpsReconciliationResult::where('rule_id', $rule->id)->delete();

        if ($rule->target_domain === 'payroll') {
            $employees = Employee::where('tenant_id', $tenantId)->where('employment_status', 'active')->get();

            foreach ($employees as $emp) {
                // Check if employee has payroll compensation set up
                $hasPayroll = EmployeeCompensation::where('employee_id', $emp->id)->exists();

                $status = $hasPayroll ? 'matched' : 'missing';
                $details = $hasPayroll ? 'Employee has active payroll compensation record.' : 'Active employee is missing payroll compensation configuration.';

                $res = OpsReconciliationResult::create([
                    'tenant_id' => $tenantId,
                    'rule_id' => $rule->id,
                    'employee_id' => $emp->id,
                    'reconciliation_status' => $status,
                    'discrepancy_details' => $details,
                    'checked_at' => now(),
                ]);

                $results[] = $res;
            }
        }

        return $results;
    }
}
