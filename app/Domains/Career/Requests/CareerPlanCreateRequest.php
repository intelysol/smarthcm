<?php

namespace App\Domains\Career\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CareerPlanCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_job_id' => ['nullable', 'string', 'exists:organization_job_definitions,id'],
            'target_date' => ['nullable', 'date'],
            'visibility' => ['nullable', 'string', 'in:private,manager,hr,talent_team'],
        ];
    }
}
