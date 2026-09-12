<?php

namespace App\Domains\Benefits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRetirementPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:150'],
            'plan_type' => ['required', 'string', 'max:40'],
            'provider' => ['nullable', 'string', 'max:150'],
            'default_employee_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_employer_match_rate' => ['required', 'numeric', 'min:0', 'max:200'],
            'max_employer_contribution_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'contribution_base' => ['nullable', 'string', 'max:40'],
            'vesting_type' => ['nullable', 'string', 'max:40'],
        ];
    }
}
