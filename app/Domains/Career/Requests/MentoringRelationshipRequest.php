<?php

namespace App\Domains\Career\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MentoringRelationshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_id' => ['nullable', 'string', 'exists:career_mentoring_programs,id'],
            'mentor_employee_id' => ['required', 'string', 'exists:employees,id'],
            'mentee_employee_id' => ['required', 'string', 'exists:employees,id', 'different:mentor_employee_id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
