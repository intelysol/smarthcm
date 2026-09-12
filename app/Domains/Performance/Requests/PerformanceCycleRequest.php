<?php

namespace App\Domains\Performance\Requests;

use App\Domains\Shared\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PerformanceCycleRequest extends BaseRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission($this->isMethod('post') ? 'hcm.performance.cycle.manage' : 'hcm.performance.cycle.manage') ?? false; }
    public function rules(): array { return ['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000'], 'cycle_type' => ['required', Rule::in(['annual', 'semi_annual', 'quarterly', 'probation', 'project', 'custom'])], 'start_date' => ['required', 'date'], 'end_date' => ['required', 'date', 'after:start_date'], 'self_review_start' => ['nullable', 'date'], 'self_review_end' => ['nullable', 'date', 'after_or_equal:self_review_start'], 'manager_review_start' => ['nullable', 'date'], 'manager_review_end' => ['nullable', 'date', 'after_or_equal:manager_review_start'], 'calibration_start' => ['nullable', 'date'], 'calibration_end' => ['nullable', 'date', 'after_or_equal:calibration_start'], 'finalization_date' => ['nullable', 'date']]; }
}
