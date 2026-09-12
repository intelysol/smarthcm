<?php

namespace App\Domains\WorkforcePlanning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkforcePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:80',
            'name' => 'required|string|max:150',
            'planning_cycle' => 'required|string|max:50',
            'planning_type' => 'nullable|string|in:annual,multi_year,quarterly,strategic,project',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'currency' => 'nullable|string|max:10',
            'company_id' => 'nullable|uuid',
            'business_unit_id' => 'nullable|uuid',
            'department_id' => 'nullable|uuid',
            'description' => 'nullable|string|max:2000',
        ];
    }
}
