<?php

namespace App\Domains\Learning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'session_id' => ['nullable', 'uuid', 'exists:learning_sessions,id'],
            'course_version_id' => ['nullable', 'uuid', 'exists:learning_course_versions,id'],
            'program_id' => ['nullable', 'uuid', 'exists:learning_programs,id'],
            'path_id' => ['nullable', 'uuid', 'exists:learning_paths,id'],
            'enrollment_type' => ['nullable', 'string', 'in:self,manager,hr,mandatory,system,performance,compliance'],
        ];
    }
}
