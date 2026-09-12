<?php

namespace App\Domains\Engagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActionPlanCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'campaign_id' => ['nullable', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scope_type' => ['required', 'string', 'in:company,department,location,team'],
            'scope_id' => ['nullable', 'uuid'],
            'owner_id' => ['nullable', 'uuid'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['required_with:items', 'string'],
            'items.*.due_date' => ['nullable', 'date'],
        ];
    }
}
