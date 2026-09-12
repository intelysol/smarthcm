<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnonymousReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string', 'uuid'],
            'case_type_id' => ['required', 'string', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'incident_date' => ['nullable', 'date'],
            'incident_location' => ['nullable', 'string', 'max:255'],
            'subject_name' => ['nullable', 'string', 'max:150'],
            'subject_type' => ['nullable', 'string', 'in:employee,former_employee,applicant,manager,department,workplace,external_party'],
            'urgency' => ['nullable', 'string', 'in:low,normal,high,urgent,critical'],
        ];
    }
}
