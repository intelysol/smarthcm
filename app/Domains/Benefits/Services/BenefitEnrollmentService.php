<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\EnrollmentStatus;
use App\Domains\Benefits\Enums\EnrollmentType;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitEnrollmentWindow;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BenefitEnrollmentService
{
    public function __construct(
        protected BenefitEligibilityService $eligibilityService
    ) {}

    public function enroll(Employee $employee, BenefitPlan $plan, array $data): BenefitEnrollment
    {
        if (! $this->eligibilityService->isEmployeeEligible($employee, $plan)) {
            throw ValidationException::withMessages([
                'benefit_plan' => "Employee {$employee->employee_number} does not meet eligibility requirements for {$plan->name}.",
            ]);
        }

        return DB::transaction(function () use ($employee, $plan, $data) {
            $latestVersion = $plan->versions()->where('is_active', true)->first();

            $employeeCost = isset($data['employee_contribution'])
                ? (float) $data['employee_contribution']
                : (float) ($latestVersion ? $latestVersion->employee_cost : $plan->employee_cost);

            $employerCost = isset($data['employer_contribution'])
                ? (float) $data['employer_contribution']
                : (float) ($latestVersion ? $latestVersion->employer_cost : $plan->employer_cost);

            $enrollment = BenefitEnrollment::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'benefit_plan_id' => $plan->id,
                'benefit_enrollment_window_id' => $data['benefit_enrollment_window_id'] ?? null,
                'benefit_plan_version_id' => $latestVersion ? $latestVersion->id : null,
                'enrollment_type' => $data['enrollment_type'] ?? EnrollmentType::OPEN_ENROLLMENT->value,
                'coverage_level' => $data['coverage_level'] ?? $plan->coverage_level,
                'effective_from' => $data['effective_from'] ?? now()->toDateString(),
                'effective_to' => $data['effective_to'] ?? null,
                'employee_contribution' => $employeeCost,
                'employer_contribution' => $employerCost,
                'currency' => $plan->currency ?? 'USD',
                'status' => $data['status'] ?? EnrollmentStatus::SUBMITTED->value,
                'notes' => $data['notes'] ?? null,
            ]);

            return $enrollment;
        });
    }

    public function approveEnrollment(BenefitEnrollment $enrollment, User $approver): BenefitEnrollment
    {
        $enrollment->update([
            'status' => EnrollmentStatus::APPROVED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $enrollment;
    }

    public function activateEnrollment(BenefitEnrollment $enrollment): BenefitEnrollment
    {
        $enrollment->update(['status' => EnrollmentStatus::ACTIVE->value]);
        return $enrollment;
    }

    public function cancelEnrollment(BenefitEnrollment $enrollment, string $reason = ''): BenefitEnrollment
    {
        $enrollment->update([
            'status' => EnrollmentStatus::CANCELLED->value,
            'effective_to' => now()->toDateString(),
            'notes' => trim(($enrollment->notes ?? '') . " [Cancelled: {$reason}]"),
        ]);

        return $enrollment;
    }
}
