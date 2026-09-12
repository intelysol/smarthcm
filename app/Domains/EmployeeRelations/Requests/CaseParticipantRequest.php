<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaseParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'participant_type' => ['required', 'string', 'in:subject,reporter,witness,investigator,manager,hr,reviewer,decision_maker,legal,support'],
            'employee_id' => ['nullable', 'string', 'uuid'],
            'user_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'role_title' => ['nullable', 'string', 'max:100'],
            'is_anonymous' => ['nullable', 'boolean'],
            'access_restricted' => ['nullable', 'boolean'],
        ];
    }
}
