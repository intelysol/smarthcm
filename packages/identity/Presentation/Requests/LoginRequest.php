<?php

declare(strict_types=1);

namespace Flow\Identity\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['tenant' => ['required', 'string', 'max:100'], 'identifier' => ['required', 'string', 'max:255'], 'password' => ['required', 'string'], 'remember' => ['sometimes', 'boolean']]; }
}
