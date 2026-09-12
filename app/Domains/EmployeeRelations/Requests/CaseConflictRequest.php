<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaseConflictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'declaration' => ['required', 'string', 'in:no_conflict,conflict_exists,potential_conflict'],
            'reason' => ['nullable', 'string'],
            'relationship_type' => ['nullable', 'string', 'in:manages_subject,close_reporting,named_in_complaint,is_reporter,is_witness,self_declared,other'],
        ];
    }
}
