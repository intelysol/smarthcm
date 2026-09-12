<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RosterAssignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'roster_period_id' => ['required', 'string', 'uuid'],
            'employee_id' => ['required', 'string', 'uuid'],
            'date' => ['required', 'date'],
            'shift_definition_id' => ['nullable', 'string', 'uuid'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
