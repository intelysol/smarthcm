<?php

namespace App\Domains\Payroll\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payroll_run_id' => 'required|uuid',
            'payment_method' => 'nullable|string|in:bank_transfer,direct_deposit,cheque,cash',
            'bank_format' => 'nullable|string|in:standard_csv,nacha,sepa,iso20022',
        ];
    }
}
