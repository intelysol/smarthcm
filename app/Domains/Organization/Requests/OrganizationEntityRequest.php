<?php

namespace App\Domains\Organization\Requests;

use App\Domains\Organization\Support\OrganizationEntityRegistry;
use App\Domains\Shared\Requests\BaseRequest;

class OrganizationEntityRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $definition = app(OrganizationEntityRegistry::class)->get((string) $this->route('entity'));
        $action = $this->isMethod('post') ? 'create' : 'update';

        return $this->user()?->hasPermission("{$definition->permissionPrefix}.{$action}") === true;
    }

    public function rules(): array
    {
        $rules = app(OrganizationEntityRegistry::class)->get((string) $this->route('entity'))->rules;

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            foreach ($rules as $field => $fieldRules) {
                $ruleList = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
                array_unshift($ruleList, 'sometimes');
                $rules[$field] = array_values(array_unique($ruleList));
            }
        }

        return $rules;
    }
}
