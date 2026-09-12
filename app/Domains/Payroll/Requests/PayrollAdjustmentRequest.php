<?php

namespace App\Domains\Payroll\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid',
            'payroll_period_id' => 'nullable|uuid',
            'adjustment_type' => 'required|string|in:earning,deduction,arrear,reimbursement',
            'code' => 'nullable|string|max:60',
            'title' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0.01',
            'effective_date' => 'nullable|date',
            'reason' => 'required|string',
        ];
    }
}
