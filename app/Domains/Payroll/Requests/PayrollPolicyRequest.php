<?php

namespace App\Domains\Payroll\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:60',
            'name' => 'required|string|max:150',
            'proration_method' => 'nullable|string|in:calendar_days,working_days,fixed_30_days,actual_hours,none',
            'rounding_method' => 'nullable|string|in:half_up,half_down,bankers,floor,ceil',
            'overtime_rate_multiplier' => 'nullable|numeric|min:1',
            'variance_threshold_percentage' => 'nullable|numeric|min:0',
        ];
    }
}
