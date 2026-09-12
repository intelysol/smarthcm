<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HearingOutcomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'summary' => ['required', 'string'],
            'recommendations' => ['nullable', 'string'],
        ];
    }
}
