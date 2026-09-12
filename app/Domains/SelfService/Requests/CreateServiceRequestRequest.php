<?php

namespace App\Domains\SelfService\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hr_service_definition_id' => 'required|uuid',
            'subject' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'priority' => 'nullable|string',
            'form_data' => 'nullable|array',
            'source_domain_module' => 'nullable|string|max:50',
            'source_entity_type' => 'nullable|string|max:80',
            'source_entity_id' => 'nullable|string|max:80',
        ];
    }
}
