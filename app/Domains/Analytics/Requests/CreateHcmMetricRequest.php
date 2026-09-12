<?php

namespace App\Domains\Analytics\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateHcmMetricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:80',
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'formula' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:30',
            'aggregation' => 'nullable|string|max:30',
            'sensitivity' => 'nullable|string|max:30',
            'refresh_frequency' => 'nullable|string|max:30',
            'effective_from' => 'nullable|date',
            'data_sources' => 'nullable|array',
        ];
    }
}
