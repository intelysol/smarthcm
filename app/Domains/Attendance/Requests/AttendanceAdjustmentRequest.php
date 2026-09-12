<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'uuid'],
            'adjustment_date' => ['required', 'date'],
            'adjustment_type' => ['required', 'string', 'in:missed_punch,time_correction,break_correction,absence_override,full_day_credit'],
            'requested_values' => ['required', 'array'],
            'reason' => ['required', 'string', 'max:1000'],
            'supporting_document_id' => ['nullable', 'string', 'uuid'],
        ];
    }
}
