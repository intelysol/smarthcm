<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\RetirementAccount;
use App\Domains\Benefits\Models\RetirementEnrollment;
use App\Domains\Benefits\Models\RetirementPlan;
use App\Domains\Employee\Models\Employee;
use Illuminate\Support\Facades\DB;

class RetirementEnrollmentService
{
    public function enrollEmployee(Employee $employee, RetirementPlan $plan, array $data = []): RetirementEnrollment
    {
        return DB::transaction(function () use ($employee, $plan, $data) {
            $employeeRate = isset($data['employee_contribution_rate'])
                ? (float) $data['employee_contribution_rate']
                : (float) $plan->default_employee_rate;

            $matchRate = (float) ($plan->default_employer_match_rate ?: 100);
            $maxRate = (float) ($plan->max_employer_contribution_rate ?: 100);
            $employerRate = min(
                $employeeRate * ($matchRate / 100),
                $maxRate
            );

            $enrollment = RetirementEnrollment::updateOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                    'retirement_plan_id' => $plan->id,
                ],
                [
                    'enrollment_date' => $data['enrollment_date'] ?? now()->toDateString(),
                    'employee_contribution_rate' => $employeeRate,
                    'employer_contribution_rate' => $employerRate,
                    'voluntary_additional_amount' => (float) ($data['voluntary_additional_amount'] ?? 0),
                    'status' => 'active',
                ]
            );

            // Initialize or find account
            RetirementAccount::firstOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                    'retirement_plan_id' => $plan->id,
                ],
                [
                    'account_number' => 'RET-' . strtoupper(substr(md5($employee->id . $plan->id), 0, 10)),
                    'currency' => $plan->currency ?? 'USD',
                    'opening_balance' => 0,
                    'total_employee_contributions' => 0,
                    'total_employer_contributions' => 0,
                    'total_investment_returns' => 0,
                    'total_withdrawals' => 0,
                    'current_balance' => 0,
                    'vested_balance' => 0,
                    'status' => 'active',
                ]
            );

            return $enrollment;
        });
    }
}
