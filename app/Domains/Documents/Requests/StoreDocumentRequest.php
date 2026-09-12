<?php
namespace App\Domains\Documents\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('documents.manage') ?? false; }
    public function rules(): array { return ['file' => ['required', 'file', 'max:512000'], 'title' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'tags' => ['nullable', 'array'], 'module' => ['nullable', 'string', 'max:60'], 'related_type' => ['nullable', 'string', 'max:120'], 'related_id' => ['nullable', 'string', 'max:80'], 'folder_id' => ['nullable', 'uuid'], 'classification' => ['nullable', 'string', 'max:40'], 'retention_policy' => ['nullable', 'string', 'max:80']]; }
}
