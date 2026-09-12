<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestigationQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_role' => ['required', 'string', 'in:reporter,subject,witness,investigator'],
            'participant_id' => ['nullable', 'string', 'uuid'],
            'question' => ['required', 'string'],
            'expected_response_type' => ['nullable', 'string', 'in:text,boolean,rating,file'],
            'answer' => ['nullable', 'string'],
        ];
    }
}
