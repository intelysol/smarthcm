<?php

namespace App\Domains\Expenses\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateExpensePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'policy_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'daily_meal_limit' => 'nullable|numeric|min:0',
            'daily_hotel_limit' => 'nullable|numeric|min:0',
            'receipt_required_threshold' => 'nullable|numeric|min:0',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
        ];
    }
}
