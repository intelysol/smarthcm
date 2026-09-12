<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OvertimeRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'uuid'],
            'overtime_date' => ['required', 'date'],
            'requested_overtime_minutes' => ['required', 'integer', 'min:1'],
            'overtime_type' => ['nullable', 'string', 'in:regular_ot,weekend_ot,holiday_ot,night_ot'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
