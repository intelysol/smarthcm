<?php

namespace App\Domains\Career\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TalentPlacementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'exists:employees,id'],
            'performance_rating' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'potential_rating' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'readiness_level' => ['nullable', 'string', 'in:not_ready,developing,nearly_ready,ready,ready_now'],
            'retention_risk' => ['nullable', 'string', 'in:low,medium,high,critical'],
            'vacancy_risk' => ['nullable', 'string', 'in:low,medium,high,critical'],
            'mobility_rating' => ['nullable', 'string'],
            'development_priority' => ['nullable', 'string'],
            'manager_notes' => ['nullable', 'string'],
        ];
    }
}
