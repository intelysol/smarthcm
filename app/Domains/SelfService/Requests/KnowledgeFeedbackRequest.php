<?php

namespace App\Domains\SelfService\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KnowledgeFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_helpful' => 'required|boolean',
            'comments' => 'nullable|string',
        ];
    }
}
