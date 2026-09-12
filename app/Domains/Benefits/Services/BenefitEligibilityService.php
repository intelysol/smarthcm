<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitEligibilityResult;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BenefitEligibilityService
{
    /**
     * Evaluate employee eligibility for a plan with detailed criteria breakdown and effective dating.
     */
    public function evaluateEligibility(
        Employee $employee,
        BenefitPlan $plan,
        ?string $asOfDate = null,
        bool $persist = true
    ): BenefitEligibilityResult {
        $evalDate = $asOfDate ? Carbon::parse($asOfDate) : Carbon::today();
        $criteriaTrace = [];
        $status = 'eligible';
        $reasons = [];

        // 1. Plan status & effective dates check
        if ($plan->status !== 'active') {
            $status = 'not_eligible';
            $reasons[] = "Plan '{$plan->name}' is inactive.";
            $criteriaTrace['plan_active'] = false;
        } else {
            $criteriaTrace['plan_active'] = true;
        }

        if ($plan->effective_from && Carbon::parse($plan->effective_from)->isAfter($evalDate)) {
            $status = 'not_eligible';
            $reasons[] = "Plan effective date is in the future ({$plan->effective_from}).";
            $criteriaTrace['plan_effective_from'] = false;
        }

        if ($plan->effective_to && Carbon::parse($plan->effective_to)->isBefore($evalDate)) {
            $status = 'not_eligible';
            $reasons[] = "Plan has expired ({$plan->effective_to}).";
            $criteriaTrace['plan_effective_to'] = false;
        }

        // 2. Employee Active Status Check
        if ($employee->employment_status !== 'active') {
            $status = 'not_eligible';
            $reasons[] = "Employee employment status is '{$employee->employment_status}' (must be active).";
            $criteriaTrace['employment_status'] = false;
        } else {
            $criteriaTrace['employment_status'] = true;
        }

        // 3. Waiting period & Effective Date Calculation
        $effectiveDate = $evalDate->copy();
        $waitingPeriodDays = (int) ($plan->waiting_period_days ?? 0);
        if ($employee->joining_date) {
            $joiningDate = Carbon::parse($employee->joining_date);
            $eligibleOnDate = $joiningDate->copy()->addDays($waitingPeriodDays);

            if ($evalDate->isBefore($eligibleOnDate)) {
                if ($status === 'eligible') {
                    $status = 'pending';
                }
                $reasons[] = "Waiting period of {$waitingPeriodDays} days not completed. Eligible on {$eligibleOnDate->toDateString()}.";
                $criteriaTrace['waiting_period'] = [
                    'required_days' => $waitingPeriodDays,
                    'joining_date' => $joiningDate->toDateString(),
                    'eligible_date' => $eligibleOnDate->toDateString(),
                    'met' => false,
                ];
                $effectiveDate = $eligibleOnDate;
            } else {
                $criteriaTrace['waiting_period'] = [
                    'required_days' => $waitingPeriodDays,
                    'met' => true,
                ];
                $effectiveDate = $eligibleOnDate;
            }
        }

        // 4. Configured Eligibility Rules
        $rules = $plan->eligibilityRules()->where('is_active', true)->get();
        foreach ($rules as $rule) {
            $criteria = $rule->criteria ?? [];

            // A. Legal Entity / Company filter
            if (! empty($criteria['company_ids']) && ! in_array($employee->company_id, $criteria['company_ids'], true)) {
                $status = 'not_eligible';
                $reasons[] = "Company {$employee->company_id} is not within eligible entities.";
                $criteriaTrace['company'] = false;
            }

            // B. Department filter
            if (! empty($criteria['department_ids']) && ! in_array($employee->department_id, $criteria['department_ids'], true)) {
                $status = 'not_eligible';
                $reasons[] = "Department {$employee->department_id} is not eligible.";
                $criteriaTrace['department'] = false;
            }

            // C. Job Grade filter
            if (! empty($criteria['job_grade_ids']) && ! in_array($employee->job_grade_id, $criteria['job_grade_ids'], true)) {
                $status = 'not_eligible';
                $reasons[] = "Job Grade {$employee->job_grade_id} is not eligible.";
                $criteriaTrace['job_grade'] = false;
            }

            // D. Employment Type filter
            if (! empty($criteria['employment_type_ids']) && ! in_array($employee->employment_type_id, $criteria['employment_type_ids'], true)) {
                $status = 'not_eligible';
                $reasons[] = "Employment Type {$employee->employment_type_id} is not eligible.";
                $criteriaTrace['employment_type'] = false;
            }

            // E. Work Location filter
            if (! empty($criteria['location_ids']) && ! in_array($employee->work_location_id, $criteria['location_ids'], true)) {
                $status = 'not_eligible';
                $reasons[] = "Work Location {$employee->work_location_id} is not eligible.";
                $criteriaTrace['work_location'] = false;
            }

            // F. Minimum service months
            if (! empty($criteria['min_service_months']) && $employee->joining_date) {
                $serviceMonths = Carbon::parse($employee->joining_date)->diffInMonths($evalDate);
                if ($serviceMonths < (int) $criteria['min_service_months']) {
                    $status = 'not_eligible';
                    $reasons[] = "Requires at least {$criteria['min_service_months']} months of service (current: {$serviceMonths}).";
                    $criteriaTrace['min_service_months'] = false;
                }
            }

            // G. Age restrictions
            if ($employee->date_of_birth) {
                $age = Carbon::parse($employee->date_of_birth)->diffInYears($evalDate);
                if (! empty($criteria['min_age']) && $age < (int) $criteria['min_age']) {
                    $status = 'not_eligible';
                    $reasons[] = "Employee age {$age} is below minimum required age {$criteria['min_age']}.";
                    $criteriaTrace['min_age'] = false;
                }
                if (! empty($criteria['max_age']) && $age > (int) $criteria['max_age']) {
                    $status = 'not_eligible';
                    $reasons[] = "Employee age {$age} exceeds maximum permitted age {$criteria['max_age']}.";
                    $criteriaTrace['max_age'] = false;
                }
            }

            // H. Strict flag review
            if ($status === 'not_eligible' && ! ($rule->is_strict ?? true)) {
                $status = 'requires_review';
            }
        }

        $reasonText = empty($reasons) ? 'Eligible for enrollment.' : implode(' ', $reasons);

        $resultData = [
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'benefit_plan_id' => $plan->id,
            'evaluation_date' => $evalDate->toDateString(),
            'status' => $status,
            'reason' => $reasonText,
            'criteria_evaluation' => $criteriaTrace,
            'effective_date' => $effectiveDate->toDateString(),
        ];

        if ($persist) {
            return BenefitEligibilityResult::create($resultData);
        }

        return new BenefitEligibilityResult($resultData);
    }

    /**
     * Backward-compatible simple boolean check
     */
    public function isEmployeeEligible(Employee $employee, BenefitPlan $plan): bool
    {
        $evaluation = $this->evaluateEligibility($employee, $plan, null, false);
        return $evaluation->status === 'eligible';
    }

    /**
     * Get all active benefit plans an employee is currently eligible for.
     */
    public function getEligiblePlansForEmployee(Employee $employee, ?string $asOfDate = null): Collection
    {
        $plans = BenefitPlan::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('status', 'active')
            ->with(['coverages', 'category', 'provider', 'program'])
            ->get();

        return $plans->filter(function (BenefitPlan $plan) use ($employee, $asOfDate) {
            $eval = $this->evaluateEligibility($employee, $plan, $asOfDate, false);
            return $eval->status === 'eligible';
        })->values();
    }

    /**
     * Bulk evaluate eligibility across a tenant population.
     */
    public function batchEvaluate(string $tenantId, ?string $planId = null): array
    {
        $employees = Employee::query()->where('tenant_id', $tenantId)->where('employment_status', 'active')->get();
        $plansQuery = BenefitPlan::query()->where('tenant_id', $tenantId)->where('status', 'active');
        if ($planId) {
            $plansQuery->where('id', $planId);
        }
        $plans = $plansQuery->get();

        $stats = [
            'total_evaluated' => 0,
            'eligible' => 0,
            'not_eligible' => 0,
            'pending' => 0,
            'requires_review' => 0,
        ];

        foreach ($employees as $emp) {
            foreach ($plans as $plan) {
                $res = $this->evaluateEligibility($emp, $plan, null, true);
                $stats['total_evaluated']++;
                if (isset($stats[$res->status])) {
                    $stats[$res->status]++;
                }
            }
        }

        return $stats;
    }
}
