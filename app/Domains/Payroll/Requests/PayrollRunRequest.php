<?php

namespace App\Domains\Payroll\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payroll_period_id' => 'required|uuid',
            'name' => 'nullable|string|max:150',
            'run_type' => 'nullable|string|in:regular,off_cycle,final_settlement,bonus,correction,emergency',
        ];
    }
}
