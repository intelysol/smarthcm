<?php

namespace App\Domains\Learning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
        ];
    }
}
