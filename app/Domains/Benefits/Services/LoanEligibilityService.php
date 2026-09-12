<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;

class LoanEligibilityService
{
    public function evaluateEligibility(Employee $employee, LoanProduct $product, float $requestedAmount): array
    {
        $errors = [];

        // 1. Min service months
        if ($employee->joining_date && $product->min_service_months > 0) {
            $serviceMonths = Carbon::parse($employee->joining_date)->diffInMonths(now());
            if ($serviceMonths < $product->min_service_months) {
                $errors[] = "Employee service ({$serviceMonths} months) is below required minimum of {$product->min_service_months} months.";
            }
        }

        // 2. Minimum & Maximum amount
        if ($requestedAmount < (float) $product->minimum_amount) {
            $errors[] = "Requested amount (\${$requestedAmount}) is below product minimum of \${$product->minimum_amount}.";
        }
        if ($requestedAmount > (float) $product->maximum_amount) {
            $errors[] = "Requested amount (\${$requestedAmount}) exceeds product maximum of \${$product->maximum_amount}.";
        }

        // 3. Existing active loans count
        $activeLoansCount = LoanApplication::where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'disbursed'])
            ->count();

        if ($activeLoansCount >= 2 && $product->loan_type !== 'salary_advance') {
            $errors[] = "Employee has {$activeLoansCount} active loans (maximum 2 permitted).";
        }

        return [
            'is_eligible' => empty($errors),
            'errors' => $errors,
        ];
    }
}
