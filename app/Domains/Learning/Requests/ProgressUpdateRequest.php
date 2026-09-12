<?php

namespace App\Domains\Learning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgressUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'enrollment_id' => ['required', 'uuid', 'exists:learning_enrollments,id'],
            'progress_percentage' => ['required', 'numeric', 'between:0,100'],
            'time_spent_seconds' => ['nullable', 'integer', 'min:0'],
            'video_watched_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'status' => ['nullable', 'string', 'in:not_started,in_progress,completed'],
        ];
    }
}
