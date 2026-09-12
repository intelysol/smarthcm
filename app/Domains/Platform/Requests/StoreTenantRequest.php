<?php

namespace App\Domains\Platform\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_platform_admin || $this->user()?->hasPermission('platform.tenants.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], 'legal_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('tenants', 'slug')],
            'tenant_code' => ['nullable', 'alpha_dash', 'max:50', Rule::unique('tenants', 'tenant_code')],
            'timezone' => ['required', 'timezone'], 'locale' => ['sometimes', 'string', 'max:10'],
            'currency' => ['required', 'string', 'size:3'], 'country_code' => ['nullable', 'string', 'size:2'],
            'primary_email' => ['nullable', 'email'], 'primary_phone' => ['nullable', 'string', 'max:40'], 'website' => ['nullable', 'url'],
        ];
    }
}
