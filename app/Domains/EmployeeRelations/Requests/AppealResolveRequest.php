<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppealResolveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', 'in:upheld,partially_upheld,overturned,rejected,withdrawn,modified'],
            'decision_reason' => ['required', 'string'],
        ];
    }
}
