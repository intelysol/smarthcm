<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'case_type_id' => ['required', 'string', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'subject_employee_id' => ['nullable', 'string', 'uuid'],
            'subject_name' => ['nullable', 'string', 'max:150'],
            'incident_date' => ['nullable', 'date'],
            'incident_location' => ['nullable', 'string', 'max:255'],
            'urgency' => ['nullable', 'string', 'in:low,normal,high,urgent,critical'],
        ];
    }
}
