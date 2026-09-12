<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Lifecycle\Enums\ImpactSeverity;
use App\Domains\Lifecycle\Models\PersonnelActionImpact;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;

class PersonnelActionImpactService
{
    public function analyzeImpact(PersonnelActionRequest $request): array
    {
        // Clear prior non-custom impacts
        PersonnelActionImpact::where('personnel_action_request_id', $request->id)->delete();

        $impacts = [];
        $changes = $request->changes;

        foreach ($changes as $change) {
            // 1. Position & Grade Changes
            if (in_array($change->field_name, ['position_id', 'designation_id'])) {
                $impacts[] = PersonnelActionImpact::create([
                    'tenant_id' => $request->tenant_id,
                    'personnel_action_request_id' => $request->id,
                    'domain' => 'position',
                    'impact_type' => 'position_changed',
                    'severity' => ImpactSeverity::INFO->value,
                    'message' => "Employee position updated from '{$change->old_value_label}' to '{$change->new_value_label}'. Core HR position occupancy will update on effective date.",
                ]);
            }

            // 2. Department & Organizational Movement
            if (in_array($change->field_name, ['department_id', 'business_unit_id', 'branch_id'])) {
                $impacts[] = PersonnelActionImpact::create([
                    'tenant_id' => $request->tenant_id,
                    'personnel_action_request_id' => $request->id,
                    'domain' => 'core_hr',
                    'impact_type' => 'organization_changed',
                    'severity' => ImpactSeverity::INFO->value,
                    'message' => "Organizational department moved to '{$change->new_value_label}'. Cost center and reporting line reassignment required.",
                ]);
            }

            // 3. Compensation & Salary Adjustments
            if (in_array($change->field_name, ['base_salary', 'basic_salary', 'salary'])) {
                $oldSalary = (float) ($change->old_value ?? 0);
                $newSalary = (float) ($change->new_value ?? 0);
                $variance = $newSalary - $oldSalary;
                $sign = $variance >= 0 ? '+' : '';

                $impacts[] = PersonnelActionImpact::create([
                    'tenant_id' => $request->tenant_id,
                    'personnel_action_request_id' => $request->id,
                    'domain' => 'payroll',
                    'impact_type' => 'salary_variance',
                    'severity' => ImpactSeverity::WARNING->value,
                    'message' => sprintf(
                        "Monthly compensation changes from %s to %s (Estimated variance: %s%s monthly). Payroll salary structure update required.",
                        number_format($oldSalary, 2),
                        number_format($newSalary, 2),
                        $sign,
                        number_format($variance, 2)
                    ),
                    'metadata' => [
                        'old_salary' => $oldSalary,
                        'new_salary' => $newSalary,
                        'monthly_variance' => $variance,
                    ],
                ]);
            }

            // 4. Job Grade Change -> Benefits Impact
            if ($change->field_name === 'job_grade_id') {
                $impacts[] = PersonnelActionImpact::create([
                    'tenant_id' => $request->tenant_id,
                    'personnel_action_request_id' => $request->id,
                    'domain' => 'benefits',
                    'impact_type' => 'benefit_reevaluation',
                    'severity' => ImpactSeverity::INFO->value,
                    'message' => "Job grade changed to '{$change->new_value_label}'. Health insurance band and executive perks re-evaluation required.",
                ]);
            }
        }

        return $impacts;
    }
}
