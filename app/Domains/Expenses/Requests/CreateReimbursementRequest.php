<?php

namespace App\Domains\Expenses\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReimbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'claim_ids' => 'required|array|min:1',
            'claim_ids.*' => 'required|uuid',
            'reimbursement_method' => 'nullable|string',
            'payroll_period_id' => 'nullable|uuid',
        ];
    }
}
