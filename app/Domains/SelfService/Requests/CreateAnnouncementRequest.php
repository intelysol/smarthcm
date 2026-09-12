<?php

namespace App\Domains\SelfService\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'priority' => 'nullable|string',
            'requires_acknowledgement' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
            'audiences' => 'nullable|array',
        ];
    }
}
