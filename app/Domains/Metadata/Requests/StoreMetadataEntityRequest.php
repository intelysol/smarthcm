<?php

namespace App\Domains\Metadata\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMetadataEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('metadata.entities.manage') ?? false;
    }

    public function rules(): array
    {
        return ['key' => ['required', 'alpha_dash', 'max:80'], 'label' => ['required', 'string', 'max:160'], 'module' => ['nullable', 'string', 'max:80'], 'entity_type' => ['required', Rule::in(['master', 'transaction', 'reference', 'lookup', 'configuration'])], 'category' => ['nullable', 'string', 'max:80'], 'icon' => ['nullable', 'string', 'max:80'], 'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'], 'description' => ['nullable', 'string', 'max:4000'], 'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])], 'settings' => ['nullable', 'array'], 'supports_soft_deletes' => ['sometimes', 'boolean'], 'supports_audit' => ['sometimes', 'boolean']];
    }
}
