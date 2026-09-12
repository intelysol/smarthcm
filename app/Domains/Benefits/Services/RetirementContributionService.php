<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\RetirementTransactionType;
use App\Domains\Benefits\Models\RetirementAccount;
use App\Domains\Benefits\Models\RetirementEnrollment;
use App\Domains\Benefits\Models\RetirementPlan;
use App\Domains\Benefits\Models\RetirementTransaction;
use App\Domains\Employee\Models\Employee;
use Illuminate\Support\Facades\DB;

class RetirementContributionService
{
    public function calculateAndPostMonthlyContribution(
        Employee $employee,
        RetirementPlan $plan,
        float $eligibleSalary,
        ?string $periodDate = null,
        ?string $sourceRefId = null
    ): RetirementTransaction {
        return DB::transaction(function () use ($employee, $plan, $eligibleSalary, $periodDate, $sourceRefId) {
            $enrollment = RetirementEnrollment::where('employee_id', $employee->id)
                ->where('retirement_plan_id', $plan->id)
                ->first();

            $empRate = $enrollment ? (float) $enrollment->employee_contribution_rate : (float) $plan->default_employee_rate;
            $emprRate = $enrollment ? (float) $enrollment->employer_contribution_rate : (float) $plan->max_employer_contribution_rate;

            $employeeAmount = round($eligibleSalary * ($empRate / 100), 4) + ($enrollment ? (float) $enrollment->voluntary_additional_amount : 0);
            $employerAmount = round($eligibleSalary * ($emprRate / 100), 4);
            $totalContribution = $employeeAmount + $employerAmount;

            $account = RetirementAccount::firstOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                    'retirement_plan_id' => $plan->id,
                ],
                [
                    'account_number' => 'RET-' . strtoupper(substr(md5($employee->id . $plan->id), 0, 10)),
                    'currency' => $plan->currency ?? 'USD',
                ]
            );

            $newBalance = (float) $account->current_balance + $totalContribution;

            // Immutable transaction entry
            $tx = $account->transactions()->create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'transaction_type' => RetirementTransactionType::EMPLOYEE_CONTRIBUTION->value,
                'transaction_date' => $periodDate ?? now()->toDateString(),
                'amount' => $totalContribution,
                'running_balance' => $newBalance,
                'currency' => $account->currency,
                'source_reference_type' => 'PayrollRun',
                'source_reference_id' => $sourceRefId,
                'description' => "Contribution (Emp: \${$employeeAmount}, Empr: \${$employerAmount})",
            ]);

            // Update running account balance
            $account->update([
                'total_employee_contributions' => (float) $account->total_employee_contributions + $employeeAmount,
                'total_employer_contributions' => (float) $account->total_employer_contributions + $employerAmount,
                'current_balance' => $newBalance,
            ]);

            return $tx;
        });
    }
}
