<?php

namespace App\Domains\Engagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyQuestionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'section_id' => ['nullable', 'uuid'],
            'bank_question_id' => ['nullable', 'uuid'],
            'question' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'question_type' => ['required', 'string', 'in:single_choice,multiple_choice,rating,likert,nps,text,long_text,numeric,yes_no,date'],
            'category' => ['required', 'string'],
            'dimension' => ['required', 'string'],
            'scale_config' => ['nullable', 'array'],
            'options' => ['nullable', 'array'],
            'is_required' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
