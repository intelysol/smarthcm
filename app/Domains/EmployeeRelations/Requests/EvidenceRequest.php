<?php

namespace App\Domains\EmployeeRelations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'evidence_type' => ['required', 'string', 'in:document,image,video,audio,email,message,system_record,statement,other'],
            'source' => ['nullable', 'string', 'max:150'],
            'file' => ['nullable', 'file', 'max:51200'], // 50MB max
            'confidentiality' => ['nullable', 'string', 'in:standard_confidential,highly_confidential,restricted,legal_restricted'],
        ];
    }
}
