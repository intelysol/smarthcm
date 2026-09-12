<?php

namespace App\Domains\Benefits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:150'],
            'loan_type' => ['required', 'string', 'max:40'],
            'minimum_amount' => ['required', 'numeric', 'min:0'],
            'maximum_amount' => ['required', 'numeric', 'min:0'],
            'max_installments' => ['required', 'integer', 'min:1', 'max:360'],
            'interest_rate_annual' => ['required', 'numeric', 'min:0'],
            'interest_method' => ['required', 'string', 'max:40'],
        ];
    }
}
