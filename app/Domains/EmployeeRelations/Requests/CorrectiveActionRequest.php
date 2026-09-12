<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectiveActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision_id' => ['nullable', 'string', 'uuid'],
            'action_type' => ['required', 'string', 'in:training,counselling,policy_acknowledgement,behavior_plan,attendance_improvement,performance_follow_up,written_warning,custom'],
            'description' => ['required', 'string'],
            'assigned_to_employee_id' => ['nullable', 'string', 'uuid'],
            'supervisor_id' => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['required', 'date'],
        ];
    }
}
