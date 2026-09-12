<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LegalHoldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string'],
            'hold_reference' => ['nullable', 'string', 'max:60'],
        ];
    }
}
