<?php

namespace App\Domains\Engagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'survey_type' => ['sometimes', 'string'],
            'instructions' => ['nullable', 'string'],
            'confidentiality_type' => ['sometimes', 'string', 'in:named,confidential,anonymous'],
            'allow_multiple_responses' => ['boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ];
    }
}
