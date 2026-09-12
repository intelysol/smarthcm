<?php

declare(strict_types=1);

namespace Flow\Identity\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['name' => ['sometimes', 'string', 'max:255'], 'phone' => ['sometimes', 'nullable', 'string', 'max:40'], 'locale' => ['sometimes', 'string', 'max:10'], 'timezone' => ['sometimes', 'nullable', 'timezone']]; }
}
