<?php

namespace App\Domains\Payroll\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_name' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'cutoff_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
            'payroll_calendar_id' => 'nullable|uuid',
            'payroll_legal_entity_id' => 'nullable|uuid',
            'currency' => 'nullable|string|size:3',
        ];
    }
}
