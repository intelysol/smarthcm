<?php

namespace App\Domains\Learning\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'uuid', 'exists:learning_categories,id'],
            'difficulty' => ['nullable', 'string', 'in:beginner,intermediate,advanced,expert'],
            'delivery_type' => ['sometimes', 'required', 'string', 'in:self_paced,classroom,virtual,blended,external'],
            'duration' => ['sometimes', 'required', 'numeric', 'min:0'],
            'duration_unit' => ['nullable', 'string', 'in:minutes,hours,days,weeks'],
            'language' => ['nullable', 'string', 'max:10'],
            'visibility' => ['nullable', 'string', 'in:public,internal,restricted,mandatory'],
            'provider_id' => ['nullable', 'uuid', 'exists:learning_providers,id'],
            'passing_score' => ['nullable', 'numeric', 'between:0,100'],
            'credit_points' => ['nullable', 'numeric', 'min:0'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'requires_attendance' => ['nullable', 'boolean'],
            'min_attendance_percentage' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}
