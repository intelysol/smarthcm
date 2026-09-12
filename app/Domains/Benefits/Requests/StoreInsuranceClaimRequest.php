<?php

namespace App\Domains\Benefits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInsuranceClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'uuid'],
            'insurance_policy_id' => ['nullable', 'uuid'],
            'benefit_enrollment_id' => ['nullable', 'uuid'],
            'claim_type' => ['required', 'string', 'max:40'],
            'incident_date' => ['required', 'date'],
            'service_provider_name' => ['nullable', 'string', 'max:150'],
            'claimed_amount' => ['required', 'numeric', 'min:0.01'],
            'is_sensitive_medical' => ['nullable', 'boolean'],
            'diagnosis_details' => ['nullable', 'string'],
            'lines' => ['nullable', 'array'],
            'lines.*.item_description' => ['required_with:lines', 'string', 'max:200'],
            'lines.*.claimed_amount' => ['required_with:lines', 'numeric', 'min:0.01'],
        ];
    }
}
