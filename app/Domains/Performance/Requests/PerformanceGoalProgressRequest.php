<?php

namespace App\Domains\Performance\Requests;

use App\Domains\Shared\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PerformanceGoalProgressRequest extends BaseRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('hcm.performance.goal.edit') ?? false; }
    public function rules(): array { return ['new_value' => ['nullable', 'numeric'], 'progress_percentage' => ['required', 'numeric', 'between:0,100'], 'comment' => ['nullable', 'string', 'max:5000'], 'source' => ['required', Rule::in(['manual', 'manager', 'employee', 'integration', 'system'])]]; }
}
