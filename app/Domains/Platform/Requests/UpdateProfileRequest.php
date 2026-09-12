<?php

namespace App\Domains\Platform\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['name' => ['sometimes', 'string', 'max:255'], 'locale' => ['sometimes', 'string', 'max:10'], 'timezone' => ['sometimes', 'nullable', 'timezone'], 'first_name' => ['sometimes', 'nullable', 'string', 'max:100'], 'last_name' => ['sometimes', 'nullable', 'string', 'max:100'], 'phone' => ['sometimes', 'nullable', 'string', 'max:40'], 'job_title' => ['sometimes', 'nullable', 'string', 'max:150'], 'theme' => ['sometimes', 'in:light,dark,system']];
    }
}
