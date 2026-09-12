<?php

namespace App\Domains\Metadata\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMetadataFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('metadata.entities.manage') ?? false;
    }

    public function rules(): array
    {
        return ['key' => ['required', 'alpha_dash', 'max:80'], 'label' => ['required', 'string', 'max:160'], 'field_type' => ['required', Rule::in(['text', 'textarea', 'rich_text', 'integer', 'decimal', 'currency', 'percentage', 'boolean', 'date', 'datetime', 'time', 'email', 'phone', 'url', 'password', 'color', 'uuid', 'json', 'file', 'image', 'signature', 'qr_code', 'barcode', 'lookup', 'multi_lookup', 'user', 'department', 'branch', 'organization', 'formula', 'ai_generated', 'computed'])], 'sort_order' => ['sometimes', 'integer', 'min:0'], 'configuration' => ['nullable', 'array'], 'validation_rules' => ['nullable', 'array'], 'visibility_expression' => ['nullable', 'array'], 'formula' => ['nullable', 'array']];
    }
}
