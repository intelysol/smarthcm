<?php

namespace App\Domains\Payroll\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompensationStructureRequest extends FormRequest
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
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string',
            'components' => 'nullable|array',
            'components.*.component_id' => 'required|uuid',
            'components.*.calculation_type' => 'nullable|string',
            'components.*.default_amount' => 'nullable|numeric',
            'components.*.percentage' => 'nullable|numeric',
        ];
    }
}
