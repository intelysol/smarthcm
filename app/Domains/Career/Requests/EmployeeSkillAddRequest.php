<?php

namespace App\Domains\Career\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeSkillAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skill_id' => ['required', 'string', 'exists:career_skills,id'],
            'current_level' => ['required', 'integer', 'min:1', 'max:10'],
            'target_level' => ['nullable', 'integer', 'min:1', 'max:10'],
            'source' => ['nullable', 'string', 'in:employee,manager,assessment,performance,learning,certification,system'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
