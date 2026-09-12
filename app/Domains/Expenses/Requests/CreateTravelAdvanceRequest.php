<?php

namespace App\Domains\Expenses\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTravelAdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requested_amount' => 'required|numeric|min:1',
            'travel_request_id' => 'nullable|uuid',
            'currency' => 'nullable|string|max:10',
            'purpose' => 'nullable|string',
        ];
    }
}
