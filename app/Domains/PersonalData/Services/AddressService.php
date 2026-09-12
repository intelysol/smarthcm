<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmployeeAddress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AddressService
{
    /**
     * Retrieve all address records for an employee.
     */
    public function getAddresses(string $employeeId): Collection
    {
        return HcmEmployeeAddress::where('employee_id', $employeeId)
            ->orderByDesc('is_current')
            ->orderByDesc('effective_from')
            ->get();
    }

    /**
     * Retrieve current address of a specific type.
     */
    public function getCurrentAddress(string $employeeId, string $addressType = 'home'): ?HcmEmployeeAddress
    {
        return HcmEmployeeAddress::where('employee_id', $employeeId)
            ->where('address_type', $addressType)
            ->where('is_current', true)
            ->first();
    }

    /**
     * Add a new address with effective dating support and handle prior current addresses.
     */
    public function addAddress(string $employeeId, array $data, ?User $actor = null): HcmEmployeeAddress
    {
        return DB::transaction(function () use ($employeeId, $data) {
            $employee = Employee::findOrFail($employeeId);
            $effectiveFrom = isset($data['effective_from']) ? Carbon::parse($data['effective_from']) : Carbon::today();
            $isCurrent = $data['is_current'] ?? true;
            $addressType = $data['address_type'] ?? 'home';
            $countryCode = $data['country_code'] ?? ($data['country'] ?? 'USA');

            if ($isCurrent) {
                // Deactivate current addresses of the same type and close effective_to
                HcmEmployeeAddress::where('employee_id', $employeeId)
                    ->where('address_type', $addressType)
                    ->where('is_current', true)
                    ->update([
                        'is_current' => false,
                        'effective_to' => $effectiveFrom->copy()->subDay()->toDateString(),
                    ]);
            }

            $address = HcmEmployeeAddress::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'address_type' => $addressType,
                'address_line_1' => $data['address_line_1'],
                'address_line_2' => $data['address_line_2'] ?? null,
                'city' => $data['city'],
                'state_province' => $data['state_province'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country_code' => $countryCode,
                'district_region' => $data['district_region'] ?? null,
                'is_current' => $isCurrent,
                'effective_from' => $effectiveFrom->toDateString(),
                'effective_to' => $data['effective_to'] ?? null,
            ]);

            // Sync to Core HR Employee
            $this->syncToEmployee($employee, $address);

            return $address;
        });
    }

    /**
     * Update an address.
     */
    public function updateAddress(string $addressId, array $data, ?User $actor = null): HcmEmployeeAddress
    {
        return DB::transaction(function () use ($addressId, $data) {
            $address = HcmEmployeeAddress::findOrFail($addressId);

            if (!empty($data['is_current']) && $data['is_current'] && !$address->is_current) {
                $effectiveFrom = isset($data['effective_from']) ? Carbon::parse($data['effective_from']) : Carbon::today();
                HcmEmployeeAddress::where('employee_id', $address->employee_id)
                    ->where('address_type', $address->address_type)
                    ->where('id', '!=', $addressId)
                    ->where('is_current', true)
                    ->update([
                        'is_current' => false,
                        'effective_to' => $effectiveFrom->copy()->subDay()->toDateString(),
                    ]);
            }

            if (isset($data['country']) && !isset($data['country_code'])) {
                $data['country_code'] = $data['country'];
            }

            $address->update($data);

            $employee = Employee::find($address->employee_id);
            if ($employee && $address->is_current) {
                $this->syncToEmployee($employee, $address);
            }

            return $address->fresh();
        });
    }

    /**
     * Delete an address record.
     */
    public function deleteAddress(string $addressId): bool
    {
        $address = HcmEmployeeAddress::findOrFail($addressId);
        return (bool) $address->delete();
    }

    /**
     * Sync current address into Core HR Employee present/permanent fields.
     */
    protected function syncToEmployee(Employee $employee, HcmEmployeeAddress $address): void
    {
        if (!$address->is_current) {
            return;
        }

        $formatted = trim(collect([
            $address->address_line_1,
            $address->address_line_2,
            $address->city,
            $address->state_province,
            $address->postal_code,
            $address->country_code,
        ])->filter()->implode(', '));

        $updates = [];
        if (in_array($address->address_type, ['home', 'residential', 'mailing', 'temporary'])) {
            $updates['present_address'] = $formatted;
            $updates['city'] = $address->city;
            $updates['state'] = $address->state_province;
            $updates['country'] = $address->country_code;
            $updates['postal_code'] = $address->postal_code;
        } elseif ($address->address_type === 'permanent') {
            $updates['permanent_address'] = $formatted;
        }

        if (!empty($updates)) {
            $employee->update($updates);
        }
    }
}
