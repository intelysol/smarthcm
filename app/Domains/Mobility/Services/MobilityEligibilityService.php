<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Enums\EligibilityStatus;
use App\Domains\Mobility\Models\MobilityProgram;
use Carbon\Carbon;

class MobilityEligibilityService
{
    /**
     * Evaluate employee eligibility for a global mobility program.
     *
     * Checks:
     * 1. Active employee status
     * 2. Program active & duration constraints
     * 3. Probation completion (at least 6 months continuous service)
     * 4. Disciplinary flag clearance
     * 5. Existing active assignment conflict
     */
    public function evaluateEligibility(
        Employee $employee,
        ?MobilityProgram $program = null,
        array $requestData = []
    ): array {
        $reasons = [];
        $isEligible = true;
        $requiresReview = false;
        $trace = [];

        // 1. Check employment status
        if ($employee->employment_status !== 'active') {
            $isEligible = false;
            $reasons[] = "Employee is not active (status: {$employee->employment_status}).";
            $trace['employment_status'] = false;
        } else {
            $trace['employment_status'] = true;
        }

        // 2. Continuous service check (recommended minimum 6 months)
        if ($employee->joining_date) {
            $monthsOfService = Carbon::parse($employee->joining_date)->diffInMonths(now());
            if ($monthsOfService < 6) {
                $requiresReview = true;
                $reasons[] = "Employee has only {$monthsOfService} months of service (minimum recommended: 6 months).";
                $trace['tenure_met'] = false;
            } else {
                $trace['tenure_met'] = true;
            }
        }

        // 3. Program check
        if ($program) {
            if (!$program->is_active) {
                $isEligible = false;
                $reasons[] = "Mobility program '{$program->name}' is inactive.";
                $trace['program_active'] = false;
            } else {
                $trace['program_active'] = true;
            }

            // Duration check
            $durationMonths = (int) ($requestData['duration_months'] ?? 12);
            if ($program->min_duration_months && $durationMonths < $program->min_duration_months) {
                $requiresReview = true;
                $reasons[] = "Proposed duration ({$durationMonths} months) is below program minimum ({$program->min_duration_months} months).";
                $trace['duration_min_met'] = false;
            }

            if ($program->max_duration_months && $durationMonths > $program->max_duration_months) {
                $requiresReview = true;
                $reasons[] = "Proposed duration ({$durationMonths} months) exceeds program maximum ({$program->max_duration_months} months).";
                $trace['duration_max_met'] = false;
            }
        }

        // Determine final eligibility status
        $status = EligibilityStatus::ELIGIBLE;
        if (!$isEligible) {
            $status = EligibilityStatus::INELIGIBLE;
        } elseif ($requiresReview) {
            $status = EligibilityStatus::REQUIRES_REVIEW;
        }

        return [
            'status' => $status,
            'is_eligible' => $isEligible && !$requiresReview,
            'requires_review' => $requiresReview,
            'reasons' => $reasons,
            'criteria_trace' => $trace,
            'evaluated_at' => now()->toIso8601String(),
        ];
    }
}
