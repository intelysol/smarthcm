<?php

namespace App\Domains\Career\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SkillCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'string', 'exists:career_skill_categories,id'],
            'skill_type' => ['required', 'string', 'in:technical,functional,soft_skill,language,tool,domain,leadership'],
            'assessment_interval_months' => ['nullable', 'integer', 'min:1', 'max:120'],
        ];
    }
}
