<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RosterPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'company_id' => ['nullable', 'string', 'uuid'],
            'department_id' => ['nullable', 'string', 'uuid'],
        ];
    }
}
