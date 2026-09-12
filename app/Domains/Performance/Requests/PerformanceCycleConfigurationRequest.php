<?php

namespace App\Domains\Performance\Requests;

use App\Domains\Shared\Requests\BaseRequest;

class PerformanceCycleConfigurationRequest extends BaseRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('hcm.performance.cycle.manage') ?? false; }
    public function rules(): array { return ['goal_weight' => ['required', 'numeric', 'min:0', 'max:100'], 'competency_weight' => ['required', 'numeric', 'min:0', 'max:100'], 'feedback_weight' => ['required', 'numeric', 'min:0', 'max:100'], 'self_assessment_enabled' => ['boolean'], 'manager_assessment_enabled' => ['boolean'], 'feedback_360_enabled' => ['boolean'], 'calibration_enabled' => ['boolean'], 'pip_enabled' => ['boolean'], 'employee_acknowledgement_required' => ['boolean'], 'minimum_response_count' => ['required', 'integer', 'min:2'], 'eligibility_rules' => ['nullable', 'array'], 'feedback_groups' => ['nullable', 'array'], 'formula_components' => ['nullable', 'array']]; }
}
