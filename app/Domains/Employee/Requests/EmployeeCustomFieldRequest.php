<?php

namespace App\Domains\Employee\Requests;

use App\Domains\Shared\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class EmployeeCustomFieldRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('employee.update') === true;
    }

    public function rules(): array
    {
        return [
            'field_key' => ['required', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_]*$/'],
            'label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', Rule::in(['text', 'textarea', 'number', 'date', 'boolean', 'select', 'multi_select'])],
            'options' => ['nullable', 'array'],
            'options.*' => ['string', 'max:255'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
