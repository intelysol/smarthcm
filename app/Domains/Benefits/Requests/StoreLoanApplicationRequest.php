<?php

namespace App\Domains\Benefits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'uuid'],
            'loan_product_id' => ['required', 'uuid'],
            'requested_amount' => ['required', 'numeric', 'min:0.01'],
            'requested_tenure_months' => ['required', 'integer', 'min:1', 'max:360'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ];
    }
}
