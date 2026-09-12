<?php

namespace App\Domains\Benefits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBenefitEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'uuid'],
            'benefit_plan_id' => ['required', 'uuid'],
            'benefit_enrollment_window_id' => ['nullable', 'uuid'],
            'enrollment_type' => ['nullable', 'string', 'max:30'],
            'coverage_level' => ['nullable', 'string', 'max:40'],
            'employee_contribution' => ['nullable', 'numeric', 'min:0'],
            'employer_contribution' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }
}
