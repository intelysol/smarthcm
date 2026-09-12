<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendancePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'grace_period_minutes' => ['nullable', 'integer', 'min:0'],
            'late_threshold_minutes' => ['nullable', 'integer', 'min:0'],
            'early_departure_threshold_minutes' => ['nullable', 'integer', 'min:0'],
            'half_day_late_threshold_minutes' => ['nullable', 'integer', 'min:0'],
            'minimum_working_hours_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_minimum_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_approval_required' => ['nullable', 'boolean'],
            'rounding_interval_minutes' => ['nullable', 'integer', 'in:1,5,15,30'],
            'rounding_method' => ['nullable', 'string', 'in:nearest,floor,ceil'],
            'auto_deduct_breaks' => ['nullable', 'boolean'],
            'missing_punch_policy' => ['nullable', 'string', 'in:flag_exception,auto_clock_out,require_regularization'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
