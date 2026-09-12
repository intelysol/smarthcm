<?php

namespace App\Domains\Engagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecognitionCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'recipient_employee_id' => ['required', 'uuid'],
            'recognition_type' => ['required', 'string', 'in:peer,manager,achievement,teamwork,innovation,customer_service,leadership,values'],
            'value_tag' => ['nullable', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'visibility' => ['required', 'string', 'in:private,team,department,organization'],
        ];
    }
}
