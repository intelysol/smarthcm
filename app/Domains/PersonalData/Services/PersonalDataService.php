<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmPersonalData;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PersonalDataService
{
    /**
     * Get or initialize Personal Data record for an employee.
     */
    public function getOrCreate(string $employeeId, string $tenantId): HcmPersonalData
    {
        $personalData = HcmPersonalData::where('employee_id', $employeeId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$personalData) {
            $employee = Employee::find($employeeId);
            $personalData = HcmPersonalData::create([
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'legal_first_name' => $employee?->first_name ?? 'First',
                'legal_middle_name' => $employee?->middle_name,
                'legal_last_name' => $employee?->last_name ?? 'Last',
                'preferred_name' => $employee?->preferred_name,
                'date_of_birth' => $employee?->date_of_birth,
                'gender' => $employee?->gender,
                'marital_status' => $employee?->marital_status,
                'nationality' => $employee?->nationality,
                'personal_email' => $employee?->personal_email,
                'personal_mobile' => $employee?->mobile,
                'personal_phone' => $employee?->office_phone,
            ]);
        }

        return $personalData;
    }

    /**
     * Update personal data and keep Core HR Employee in sync for common fields.
     */
    public function update(string $employeeId, array $data, ?User $actor = null): HcmPersonalData
    {
        return DB::transaction(function () use ($employeeId, $data) {
            $employee = Employee::findOrFail($employeeId);
            $personalData = $this->getOrCreate($employeeId, $employee->tenant_id);

            // Normalize input aliases
            $normalized = $data;
            if (isset($data['first_name'])) {
                $normalized['legal_first_name'] = $data['first_name'];
            }
            if (isset($data['last_name'])) {
                $normalized['legal_last_name'] = $data['last_name'];
            }
            if (isset($data['middle_name'])) {
                $normalized['legal_middle_name'] = $data['middle_name'];
            }
            if (isset($data['personal_phone']) && !isset($data['personal_mobile'])) {
                $normalized['personal_mobile'] = $data['personal_phone'];
            }

            $personalData->update($normalized);

            // Sync to Core HR Employee
            $employeeUpdates = [];
            if (isset($normalized['legal_first_name'])) {
                $employeeUpdates['first_name'] = $normalized['legal_first_name'];
            }
            if (isset($normalized['legal_middle_name'])) {
                $employeeUpdates['middle_name'] = $normalized['legal_middle_name'];
            }
            if (isset($normalized['legal_last_name'])) {
                $employeeUpdates['last_name'] = $normalized['legal_last_name'];
            }
            if (isset($normalized['preferred_name'])) {
                $employeeUpdates['preferred_name'] = $normalized['preferred_name'];
            }
            if (isset($normalized['gender'])) {
                $employeeUpdates['gender'] = $normalized['gender'];
            }
            if (isset($normalized['marital_status'])) {
                $employeeUpdates['marital_status'] = $normalized['marital_status'];
            }
            if (isset($data['blood_group'])) {
                $employeeUpdates['blood_group'] = $data['blood_group'];
            }
            if (isset($normalized['nationality'])) {
                $employeeUpdates['nationality'] = $normalized['nationality'];
            }
            if (isset($normalized['date_of_birth'])) {
                $employeeUpdates['date_of_birth'] = $normalized['date_of_birth'];
            }
            if (isset($normalized['personal_email'])) {
                $employeeUpdates['personal_email'] = $normalized['personal_email'];
            }
            if (isset($normalized['personal_mobile'])) {
                $employeeUpdates['mobile'] = $normalized['personal_mobile'];
            }

            if (!empty($employeeUpdates)) {
                $employee->update($employeeUpdates);
            }

            return $personalData->fresh();
        });
    }
}
