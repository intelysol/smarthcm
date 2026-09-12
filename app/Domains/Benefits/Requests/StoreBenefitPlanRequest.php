<?php

namespace App\Domains\Benefits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBenefitPlanRequest extends FormRequest
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
            'benefit_type' => ['required', 'string', 'max:40'],
            'coverage_level' => ['nullable', 'string', 'max:40'],
            'employee_cost' => ['required', 'numeric', 'min:0'],
            'employer_cost' => ['required', 'numeric', 'min:0'],
            'annual_limit' => ['nullable', 'numeric', 'min:0'],
            'waiting_period_days' => ['nullable', 'integer', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }
}
