<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'shift_type' => ['required', 'string', 'in:normal,overnight,flexible,split'],
            'start_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'end_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'core_start_time' => ['nullable', 'string'],
            'core_end_time' => ['nullable', 'string'],
            'required_daily_minutes' => ['nullable', 'integer'],
            'split_second_start' => ['nullable', 'string'],
            'split_second_end' => ['nullable', 'string'],
            'grace_period_minutes' => ['nullable', 'integer', 'min:0'],
            'late_threshold_minutes' => ['nullable', 'integer', 'min:0'],
            'early_departure_threshold_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_eligible' => ['nullable', 'boolean'],
            'min_overtime_threshold_minutes' => ['nullable', 'integer', 'min:0'],
            'rounding_rule_minutes' => ['nullable', 'integer', 'in:1,5,15,30'],
            'is_night_shift' => ['nullable', 'boolean'],
            'is_flexible' => ['nullable', 'boolean'],
            'is_split' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'color_code' => ['nullable', 'string', 'max:20'],
        ];
    }
}
