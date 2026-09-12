<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaseNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note_type' => ['nullable', 'string', 'in:internal_hr_note,investigator_note,legal_note,decision_note,general_note'],
            'visibility' => ['nullable', 'string', 'in:case_team,hr_only,investigator_only,legal_only,decision_maker'],
            'content' => ['required', 'string'],
        ];
    }
}
