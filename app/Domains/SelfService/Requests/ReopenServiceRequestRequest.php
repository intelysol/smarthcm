<?php

namespace App\Domains\SelfService\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReopenServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:5',
        ];
    }
}
