<?php

namespace App\Domains\SelfService\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRequestCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string',
            'comment_type' => 'nullable|string|in:public,internal',
            'attachments' => 'nullable|array',
        ];
    }
}
