<?php

namespace App\Domains\Platform\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['tenant' => ['required', 'string', 'max:120'], 'email' => ['required', 'email:rfc', 'max:255'], 'password' => ['required', 'string'], 'remember' => ['sometimes', 'boolean']];
    }
}
