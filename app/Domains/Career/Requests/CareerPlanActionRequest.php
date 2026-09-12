<?php

namespace App\Domains\Career\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CareerPlanActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action_type' => ['required', 'string', 'in:training,mentoring,coaching,project,job_rotation,stretch_assignment,certification'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
