<?php

namespace App\Domains\Expenses\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateExpenseClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:150',
            'claim_date' => 'nullable|date',
            'currency' => 'nullable|string|max:10',
            'travel_authorization_id' => 'nullable|uuid',
            'travel_request_id' => 'nullable|uuid',
            'notes' => 'nullable|string',
        ];
    }
}
