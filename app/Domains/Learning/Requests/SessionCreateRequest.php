<?php

namespace App\Domains\Learning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'uuid', 'exists:learning_courses,id'],
            'course_version_id' => ['nullable', 'uuid', 'exists:learning_course_versions,id'],
            'provider_id' => ['nullable', 'uuid', 'exists:learning_providers,id'],
            'instructor_id' => ['nullable', 'uuid', 'exists:learning_instructors,id'],
            'venue_id' => ['nullable', 'uuid', 'exists:learning_venues,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'enrollment_deadline' => ['nullable', 'date', 'before:end_datetime'],
        ];
    }
}
