<?php

namespace App\Domains\Learning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NominationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'uuid', 'exists:employees,id'],
            'course_id' => ['required', 'uuid', 'exists:learning_courses,id'],
            'is_mandatory' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
