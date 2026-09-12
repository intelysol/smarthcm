<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmergencyContact;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EmergencyContactService
{
    /**
     * Get all emergency contacts for an employee ordered by priority.
     */
    public function getContacts(string $employeeId): Collection
    {
        return HcmEmergencyContact::where('employee_id', $employeeId)
            ->orderBy('priority')
            ->get();
    }

    /**
     * Add an emergency contact.
     */
    public function addContact(string $employeeId, array $data, ?User $actor = null): HcmEmergencyContact
    {
        return DB::transaction(function () use ($employeeId, $data) {
            $employee = Employee::findOrFail($employeeId);
            $isPrimary = $data['is_primary'] ?? false;

            // If this is the first contact, make it primary by default
            $existingCount = HcmEmergencyContact::where('employee_id', $employeeId)->count();
            if ($existingCount === 0) {
                $isPrimary = true;
            }

            if ($isPrimary) {
                HcmEmergencyContact::where('employee_id', $employeeId)
                    ->update(['is_primary' => false]);
            }

            $priority = $data['priority'] ?? ($data['priority_order'] ?? ($existingCount + 1));
            $contactName = $data['contact_name'] ?? ($data['name'] ?? 'Contact');
            $mobile = $data['mobile'] ?? ($data['primary_phone'] ?? '');
            $telephone = $data['telephone'] ?? ($data['secondary_phone'] ?? null);

            $contact = HcmEmergencyContact::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'contact_name' => $contactName,
                'relationship' => $data['relationship'],
                'mobile' => $mobile,
                'telephone' => $telephone,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'country' => $data['country'] ?? null,
                'priority' => $priority,
                'is_primary' => $isPrimary,
            ]);

            if ($isPrimary && !empty($contact->mobile)) {
                $employee->update(['emergency_phone' => $contact->mobile]);
            }

            return $contact;
        });
    }

    /**
     * Update an emergency contact.
     */
    public function updateContact(string $contactId, array $data, ?User $actor = null): HcmEmergencyContact
    {
        return DB::transaction(function () use ($contactId, $data) {
            $contact = HcmEmergencyContact::findOrFail($contactId);

            if (!empty($data['is_primary']) && $data['is_primary'] && !$contact->is_primary) {
                HcmEmergencyContact::where('employee_id', $contact->employee_id)
                    ->where('id', '!=', $contactId)
                    ->update(['is_primary' => false]);
            }

            if (isset($data['name']) && !isset($data['contact_name'])) {
                $data['contact_name'] = $data['name'];
            }
            if (isset($data['primary_phone']) && !isset($data['mobile'])) {
                $data['mobile'] = $data['primary_phone'];
            }
            if (isset($data['secondary_phone']) && !isset($data['telephone'])) {
                $data['telephone'] = $data['secondary_phone'];
            }
            if (isset($data['priority_order']) && !isset($data['priority'])) {
                $data['priority'] = $data['priority_order'];
            }

            $contact->update($data);

            if ($contact->is_primary && !empty($contact->mobile)) {
                $employee = Employee::find($contact->employee_id);
                $employee?->update(['emergency_phone' => $contact->mobile]);
            }

            return $contact->fresh();
        });
    }

    /**
     * Delete an emergency contact.
     */
    public function deleteContact(string $contactId): bool
    {
        $contact = HcmEmergencyContact::findOrFail($contactId);
        $employeeId = $contact->employee_id;
        $wasPrimary = $contact->is_primary;

        $deleted = (bool) $contact->delete();

        if ($wasPrimary) {
            // Promote next available contact to primary
            $nextContact = HcmEmergencyContact::where('employee_id', $employeeId)
                ->orderBy('priority')
                ->first();

            if ($nextContact) {
                $nextContact->update(['is_primary' => true]);
                $employee = Employee::find($employeeId);
                $employee?->update(['emergency_phone' => $nextContact->mobile]);
            }
        }

        return $deleted;
    }

    /**
     * Reorder emergency contacts.
     */
    public function reorderContacts(string $employeeId, array $orderedIds): void
    {
        DB::transaction(function () use ($employeeId, $orderedIds) {
            foreach ($orderedIds as $index => $id) {
                HcmEmergencyContact::where('id', $id)
                    ->where('employee_id', $employeeId)
                    ->update(['priority' => $index + 1]);
            }
        });
    }
}
