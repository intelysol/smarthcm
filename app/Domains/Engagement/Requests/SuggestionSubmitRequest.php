<?php

namespace App\Domains\Engagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuggestionSubmitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'is_anonymous' => ['boolean'],
        ];
    }
}
