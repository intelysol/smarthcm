<?php

namespace App\Domains\WorkforcePlanning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePositionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_code' => 'required|string|max:80',
            'title' => 'required|string|max:150',
            'department_id' => 'nullable|uuid',
            'branch_id' => 'nullable|uuid',
            'job_grade_id' => 'nullable|uuid',
            'designation_id' => 'nullable|uuid',
            'cost_center_id' => 'nullable|uuid',
            'status' => 'nullable|string|in:planned,budgeted,open,occupied,frozen,cancelled,eliminated',
            'is_budgeted' => 'nullable|boolean',
            'fte' => 'nullable|numeric|min:0.1|max:5.0',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'budget' => 'nullable|array',
            'budget.base_salary_budget' => 'nullable|numeric|min:0',
            'budget.bonus_budget' => 'nullable|numeric|min:0',
            'budget.benefits_budget' => 'nullable|numeric|min:0',
            'budget.employer_contributions_budget' => 'nullable|numeric|min:0',
            'budget.payroll_tax_budget' => 'nullable|numeric|min:0',
            'budget.recruitment_cost_budget' => 'nullable|numeric|min:0',
            'budget.equipment_cost_budget' => 'nullable|numeric|min:0',
        ];
    }
}
