<?php

namespace App\Domains\Engagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyResponseSubmitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'raw_token' => ['nullable', 'string'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'uuid'],
            'answers.*.option_id' => ['nullable', 'string'],
            'answers.*.numeric_value' => ['nullable', 'numeric'],
            'answers.*.text_value' => ['nullable', 'string'],
        ];
    }
}
