<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:150'],
            'vendor' => ['nullable', 'string', 'max:60'],
            'model' => ['nullable', 'string', 'max:80'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'ip_address' => ['nullable', 'string', 'max:60'],
            'port' => ['nullable', 'integer'],
            'location_id' => ['nullable', 'string', 'uuid'],
            'branch_id' => ['nullable', 'string', 'uuid'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'connector_type' => ['required', 'string', 'in:zkteco,biometric,rfid,mobile,csv,api'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
