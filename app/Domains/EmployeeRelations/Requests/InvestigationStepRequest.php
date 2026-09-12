<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestigationStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'step_type' => ['nullable', 'string', 'in:collect_evidence,interview_reporter,interview_subject,interview_witness,review_documents,review_system_records,prepare_findings,custom'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:pending,in_progress,completed,cancelled,overdue'],
        ];
    }
}
