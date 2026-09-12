<?php

namespace App\Domains\Analytics\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExecuteHcmReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dataset' => 'required|string|max:80',
            'dimensions' => 'nullable|array',
            'metrics' => 'nullable|array',
            'filters' => 'nullable|array',
            'grouping' => 'nullable|array',
            'format' => 'nullable|string|in:json,csv,pdf,xlsx',
        ];
    }
}
