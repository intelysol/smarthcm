<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaseTriageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recommended_case_type_id' => ['nullable', 'string', 'uuid'],
            'recommended_priority' => ['required', 'string', 'in:low,normal,high,urgent,critical'],
            'recommended_severity' => ['required', 'string', 'in:informational,minor,moderate,serious,critical'],
            'requires_investigation' => ['required', 'boolean'],
            'required_investigator_type' => ['nullable', 'string', 'max:60'],
            'conflict_of_interest_checked' => ['nullable', 'boolean'],
            'escalation_required' => ['nullable', 'boolean'],
            'escalation_reason' => ['nullable', 'string'],
            'triage_notes' => ['nullable', 'string'],
        ];
    }
}
