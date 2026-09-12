<?php

namespace App\Domains\Engagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'survey_type' => ['required', 'string', 'in:engagement,pulse,satisfaction,culture,onboarding,exit,manager_feedback,training_feedback,custom'],
            'instructions' => ['nullable', 'string'],
            'confidentiality_type' => ['required', 'string', 'in:named,confidential,anonymous'],
            'allow_multiple_responses' => ['boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
