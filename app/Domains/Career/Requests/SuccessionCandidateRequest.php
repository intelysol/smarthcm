<?php

namespace App\Domains\Career\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuccessionCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'exists:employees,id'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
            'readiness_timeframe' => ['required', 'string', 'in:ready_now,ready_under_1_year,ready_1_to_2_years,ready_2_to_3_years,long_term'],
            'readiness_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'potential_rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'performance_rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'is_emergency_choice' => ['nullable', 'boolean'],
        ];
    }
}
