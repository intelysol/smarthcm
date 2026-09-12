<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\RetirementPlan;
use App\Domains\Benefits\Models\RetirementPlanVersion;
use Illuminate\Support\Facades\DB;

class RetirementPlanService
{
    public function createPlan(array $data): RetirementPlan
    {
        return DB::transaction(function () use ($data) {
            $plan = RetirementPlan::create(array_merge([
                'default_employee_rate' => 5.0000,
                'default_employer_match_rate' => 100.0000,
                'max_employer_contribution_rate' => 5.0000,
            ], $data));

            $plan->versions()->create([
                'tenant_id' => $plan->tenant_id,
                'version_number' => 1,
                'effective_from' => now()->toDateString(),
                'employee_rate' => $plan->default_employee_rate,
                'employer_rate' => $plan->max_employer_contribution_rate,
                'is_active' => true,
            ]);

            return $plan->fresh(['versions', 'vestingRules']);
        });
    }

    public function configureVesting(RetirementPlan $plan, array $rules): void
    {
        $plan->vestingRules()->delete();

        foreach ($rules as $rule) {
            $plan->vestingRules()->create([
                'tenant_id' => $plan->tenant_id,
                'completed_years' => (int) $rule['completed_years'],
                'vesting_percentage' => (float) $rule['vesting_percentage'],
            ]);
        }
    }
}
