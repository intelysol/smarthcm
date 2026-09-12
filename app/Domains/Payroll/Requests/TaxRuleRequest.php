<?php

namespace App\Domains\Payroll\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxRuleRequest extends FormRequest
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
            'country' => 'nullable|string|size:3',
            'calculation_mode' => 'required|string|in:progressive_brackets,flat_rate,annualized,exempt',
            'flat_rate_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
