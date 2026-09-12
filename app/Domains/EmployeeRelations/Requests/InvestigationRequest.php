<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestigationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'investigator_id' => ['required', 'integer', 'exists:users,id'],
            'scope' => ['required', 'string'],
            'start_date' => ['nullable', 'date'],
            'target_completion_date' => ['nullable', 'date'],
        ];
    }
}
