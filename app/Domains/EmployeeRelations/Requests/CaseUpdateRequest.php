<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'summary' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'string', 'in:low,normal,high,urgent,critical'],
            'severity' => ['sometimes', 'string', 'in:informational,minor,moderate,serious,critical'],
            'confidentiality_level' => ['sometimes', 'string', 'in:standard_confidential,highly_confidential,restricted,legal_restricted'],
            'incident_date' => ['nullable', 'date'],
            'incident_location' => ['nullable', 'string', 'max:255'],
            'target_resolution_date' => ['nullable', 'date'],
        ];
    }
}
