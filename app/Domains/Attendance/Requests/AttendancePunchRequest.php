<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendancePunchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'string', 'uuid'],
            'event_type' => ['required', 'string', 'in:IN,OUT,BREAK_IN,BREAK_OUT,check_in,check_out,break_start,break_end'],
            'timestamp' => ['nullable', 'date'],
            'device_id' => ['nullable', 'string', 'uuid'],
            'location_id' => ['nullable', 'string', 'uuid'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ];
    }
}
