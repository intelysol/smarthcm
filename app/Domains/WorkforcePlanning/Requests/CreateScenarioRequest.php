<?php

namespace App\Domains\WorkforcePlanning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateScenarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'scenario_type' => 'required|string|in:base,growth,cost_reduction,hiring_freeze,restructuring,expansion',
            'description' => 'nullable|string|max:2000',
        ];
    }
}
