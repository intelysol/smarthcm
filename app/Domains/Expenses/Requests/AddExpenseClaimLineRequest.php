<?php

namespace App\Domains\Expenses\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddExpenseClaimLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => 'required|uuid',
            'expense_date' => 'required|date',
            'description' => 'required|string',
            'original_amount' => 'required|numeric|min:0.01',
            'original_currency' => 'nullable|string|max:10',
            'merchant' => 'nullable|string|max:120',
            'tax_rate' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'cost_center_id' => 'nullable|uuid',
            'project_id' => 'nullable|uuid',
            'is_mileage' => 'nullable|boolean',
            'mileage_distance' => 'nullable|numeric|min:0',
            'is_per_diem' => 'nullable|boolean',
            'per_diem_days' => 'nullable|numeric|min:0',
        ];
    }
}
