<?php

namespace App\Domains\Benefits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRetirementWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'retirement_account_id' => ['required', 'uuid'],
            'withdrawal_type' => ['required', 'string', 'max:40'],
            'requested_amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
