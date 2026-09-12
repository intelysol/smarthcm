<?php

namespace App\Domains\Expenses\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTravelRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination' => 'required|string|max:150',
            'purpose' => 'required|string',
            'travel_type' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'business_justification' => 'nullable|string',
            'segments' => 'nullable|array',
            'segments.*.origin' => 'required_with:segments|string|max:100',
            'segments.*.destination' => 'required_with:segments|string|max:100',
            'segments.*.departure_time' => 'required_with:segments|date',
        ];
    }
}
