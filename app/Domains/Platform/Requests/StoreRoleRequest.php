<?php

namespace App\Domains\Platform\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('platform.roles.manage') ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'alpha_dash', 'max:100'], 'label' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:2000'], 'permission_ids' => ['sometimes', 'array'], 'permission_ids.*' => ['integer', 'exists:permissions,id']];
    }
}
