<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', 'in:no_action,counselling,verbal_warning,written_warning,final_warning,training_required,corrective_action,other'],
            'reason' => ['required', 'string'],
            'effective_date' => ['nullable', 'date'],
            'is_draft' => ['nullable', 'boolean'],
            'actions' => ['nullable', 'array'],
            'actions.*.action_type' => ['required', 'string', 'in:training,counselling,policy_acknowledgement,behavior_plan,attendance_improvement,performance_follow_up,written_warning,custom'],
            'actions.*.description' => ['required', 'string'],
            'actions.*.due_date' => ['required', 'date'],
        ];
    }
}
