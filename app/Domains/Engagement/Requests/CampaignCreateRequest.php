<?php

namespace App\Domains\Engagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'timezone' => ['nullable', 'string'],
            'minimum_response_threshold' => ['nullable', 'integer', 'min:1'],
            'is_recurring' => ['boolean'],
            'audiences' => ['nullable', 'array'],
            'schedule' => ['nullable', 'array'],
        ];
    }
}
