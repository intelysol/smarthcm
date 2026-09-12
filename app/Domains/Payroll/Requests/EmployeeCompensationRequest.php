<?php

namespace App\Domains\Payroll\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeCompensationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'compensation_structure_id' => 'nullable|uuid',
            'base_salary' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'pay_frequency' => 'nullable|string|in:monthly,biweekly,weekly,semi_monthly',
            'currency' => 'nullable|string|size:3',
            'reason_for_change' => 'nullable|string|max:100',
            'components' => 'nullable|array',
            'components.*.component_id' => 'required|uuid',
            'components.*.amount' => 'nullable|numeric',
            'components.*.percentage' => 'nullable|numeric',
        ];
    }
}
