<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitCategory;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitPlanVersion;
use App\Domains\Benefits\Models\BenefitProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BenefitPlanService
{
    public function createPlan(array $data): BenefitPlan
    {
        return DB::transaction(function () use ($data) {
            $plan = BenefitPlan::create($data);

            // Create initial version 1
            $plan->versions()->create([
                'tenant_id' => $plan->tenant_id,
                'version_number' => 1,
                'effective_from' => $plan->effective_from,
                'effective_to' => $plan->effective_to,
                'employee_cost' => $plan->employee_cost,
                'employer_cost' => $plan->employer_cost,
                'annual_limit' => $plan->annual_limit,
                'coverage_details' => $plan->coverage,
                'eligibility_criteria' => $plan->eligibility_rules,
                'is_active' => true,
            ]);

            return $plan->fresh(['versions', 'category', 'provider']);
        });
    }

    public function createPlanVersion(BenefitPlan $plan, array $data): BenefitPlanVersion
    {
        return DB::transaction(function () use ($plan, $data) {
            $newVersionNumber = $plan->versions()->max('version_number') + 1;

            // Deactivate previous versions
            $plan->versions()->update(['is_active' => false]);

            $version = $plan->versions()->create(array_merge($data, [
                'tenant_id' => $plan->tenant_id,
                'version_number' => $newVersionNumber,
                'is_active' => true,
            ]));

            $plan->update([
                'version' => $newVersionNumber,
                'employee_cost' => $data['employee_cost'] ?? $plan->employee_cost,
                'employer_cost' => $data['employer_cost'] ?? $plan->employer_cost,
                'annual_limit' => $data['annual_limit'] ?? $plan->annual_limit,
                'effective_from' => $data['effective_from'] ?? $plan->effective_from,
                'effective_to' => $data['effective_to'] ?? $plan->effective_to,
            ]);

            return $version;
        });
    }
}
