<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Employee\Models\Employee;

class BenefitsSummaryProvider
{
    /**
     * Provide a consolidated benefits summary for Employee Profile (Epic 2.31).
     * Does not duplicate master data.
     */
    public function getProfileSummary(Employee $employee): array
    {
        $today = now()->toDateString();
        $enrollments = BenefitEnrollment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'active'])
            ->whereDate('effective_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $today);
            })
            ->with(['plan.category', 'dependents', 'beneficiaries'])
            ->get();

        $activeBenefits = $enrollments->map(function (BenefitEnrollment $enr) {
            return [
                'enrollment_id' => $enr->id,
                'plan_name' => $enr->plan?->name,
                'category' => $enr->plan?->category?->name ?? 'Health & Protection',
                'coverage_level' => $enr->coverage_level,
                'status' => $enr->status,
                'effective_from' => $enr->effective_from?->toDateString(),
                'employee_contribution' => (float) $enr->employee_contribution,
                'employer_contribution' => (float) $enr->employer_contribution,
                'dependents_count' => $enr->dependents->count(),
                'beneficiaries_count' => $enr->beneficiaries->count(),
            ];
        })->toArray();

        return [
            'employee_id' => $employee->id,
            'active_benefits_count' => count($activeBenefits),
            'total_monthly_employee_cost' => $enrollments->sum('employee_contribution'),
            'total_monthly_employer_cost' => $enrollments->sum('employer_contribution'),
            'currency' => $enrollments->first()?->currency ?? 'USD',
            'benefits' => $activeBenefits,
        ];
    }
}
