<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaseCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'case_type_id' => ['required', 'string', 'uuid'],
            'subject_employee_id' => ['nullable', 'string', 'uuid'],
            'subject_type' => ['nullable', 'string', 'in:employee,former_employee,applicant,manager,department,workplace,external_party'],
            'subject_name' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,urgent,critical'],
            'severity' => ['nullable', 'string', 'in:informational,minor,moderate,serious,critical'],
            'confidentiality_level' => ['nullable', 'string', 'in:standard_confidential,highly_confidential,restricted,legal_restricted'],
            'incident_date' => ['nullable', 'date'],
            'incident_location' => ['nullable', 'string', 'max:255'],
            'target_resolution_date' => ['nullable', 'date'],
            'reporter_type' => ['nullable', 'string', 'in:employee,manager,hr,anonymous,external,system'],
            'reporter_name' => ['nullable', 'string', 'max:150'],
            'reporter_email' => ['nullable', 'email', 'max:150'],
            'reporter_phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}
