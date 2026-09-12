<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'participant_id' => ['required', 'string', 'uuid'],
            'statement_type' => ['required', 'string', 'in:initial,witness,response,investigation,appeal'],
            'content' => ['required', 'string'],
            'is_confidential' => ['nullable', 'boolean'],
            'is_verified' => ['nullable', 'boolean'],
        ];
    }
}
