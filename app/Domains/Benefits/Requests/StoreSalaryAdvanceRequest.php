<?php

namespace App\Domains\Benefits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryAdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'uuid'],
            'requested_amount' => ['required', 'numeric', 'min:0.01'],
            'repayment_months' => ['nullable', 'integer', 'min:1', 'max:12'],
            'effective_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
