<?php

declare(strict_types=1);

namespace Flow\Identity\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['current_password' => ['required', 'string'], 'password' => ['required', 'string', 'confirmed', 'min:12']]; }
}
