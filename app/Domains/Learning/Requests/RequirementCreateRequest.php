<?php

namespace App\Domains\Learning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequirementCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'uuid', 'exists:learning_courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_type' => ['required', 'string', 'in:company,department,location,job,job_grade,employment_type,employee_group,individual'],
            'target_id' => ['nullable', 'uuid'],
            'deadline_type' => ['required', 'string', 'in:absolute,relative_hiring,relative_promotion,relative_assignment'],
            'due_date' => ['nullable', 'date'],
            'days_offset' => ['nullable', 'integer', 'min:1'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_interval_months' => ['nullable', 'integer', 'min:1'],
            'compliance_category' => ['nullable', 'string', 'max:100'],
        ];
    }
}
