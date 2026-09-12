<?php

namespace App\Domains\Payroll\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompensationComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:60',
            'name' => 'required|string|max:150',
            'component_type' => 'required|string',
            'calculation_type' => 'nullable|string|in:fixed,percentage_of_basic,percentage_of_gross,formula,hourly_rate,attendance_based,custom_rule',
            'default_amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'formula' => 'nullable|string|max:255',
            'is_taxable' => 'boolean',
            'is_pensionable' => 'boolean',
            'is_overtime_eligible' => 'boolean',
            'is_recurring' => 'boolean',
            'priority_order' => 'integer',
            'description' => 'nullable|string',
        ];
    }
}
