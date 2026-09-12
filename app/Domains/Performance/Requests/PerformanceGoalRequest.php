<?php

namespace App\Domains\Performance\Requests;

use App\Domains\Shared\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PerformanceGoalRequest extends BaseRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission($this->isMethod('post') ? 'hcm.performance.goal.create' : 'hcm.performance.goal.edit') ?? false; }
    public function rules(): array { return ['cycle_id' => ['required', 'uuid', 'exists:performance_cycles,id'], 'employee_id' => ['nullable', 'uuid', 'exists:employees,id'], 'parent_goal_id' => ['nullable', 'uuid', 'exists:performance_goals,id'], 'owner_type' => ['required', Rule::in(['employee', 'manager', 'team', 'department', 'organization'])], 'title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000'], 'goal_type' => ['required', Rule::in(['company', 'department', 'team', 'individual', 'development', 'project', 'custom'])], 'measurement_type' => ['required', Rule::in(['percentage', 'number', 'currency', 'duration', 'boolean', 'rating', 'milestone', 'manual'])], 'weight' => ['required', 'numeric', 'min:0', 'max:100'], 'target_value' => ['nullable', 'numeric'], 'current_value' => ['nullable', 'numeric'], 'unit' => ['nullable', 'string', 'max:30'], 'start_date' => ['nullable', 'date'], 'due_date' => ['nullable', 'date'], 'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])], 'version' => ['sometimes', 'integer', 'min:1']]; }
}
