<?php

namespace App\Domains\Rules\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBusinessRuleRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('rules.manage') ?? false; }
    public function rules(): array { return ['key' => ['required', 'alpha_dash', 'max:100'], 'name' => ['required', 'string', 'max:160'], 'description' => ['nullable', 'string', 'max:4000'], 'domain' => ['required', 'string', 'max:50'], 'category' => ['required', Rule::in(['validation', 'calculation', 'decision', 'approval', 'assignment', 'notification', 'ui', 'integration', 'automation', 'security'])], 'trigger' => ['required', 'string', 'max:50'], 'priority' => ['sometimes', 'integer', 'min:0'], 'execution_mode' => ['sometimes', Rule::in(['synchronous', 'queued', 'scheduled', 'event', 'workflow', 'api', 'batch'])], 'conditions' => ['required', 'array'], 'actions' => ['required', 'array'], 'settings' => ['nullable', 'array'], 'tags' => ['nullable', 'array'], 'effective_from' => ['nullable', 'date'], 'effective_to' => ['nullable', 'date', 'after:effective_from']]; }
}
