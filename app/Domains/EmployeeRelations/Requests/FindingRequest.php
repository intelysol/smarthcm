<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FindingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'allegation_id' => ['nullable', 'string', 'uuid'],
            'finding' => ['required', 'string', 'in:substantiated,partially_substantiated,unsubstantiated,inconclusive,withdrawn'],
            'confidence' => ['nullable', 'string', 'in:low,medium,high'],
            'rationale' => ['required', 'string'],
            'evidence_summary' => ['nullable', 'string'],
        ];
    }
}
