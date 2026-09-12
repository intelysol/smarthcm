<?php

namespace App\Domains\EmployeeProfile\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Models\EmployeeProfileAudit;
use App\Domains\EmployeeProfile\Models\EmployeeProfileChangeRequest;
use App\Domains\EmployeeProfile\Models\EmployeeProfileChangeRequestItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfileChangeRequestService
{
    protected array $allowedFields = [
        'mobile',
        'alternate_mobile',
        'personal_email',
        'emergency_phone',
        'present_address',
        'permanent_address',
        'city',
        'state',
        'postal_code',
        'marital_status',
        'photo_path',
    ];

    public function createChangeRequest(Employee $employee, array $changes, ?string $comments = null): EmployeeProfileChangeRequest
    {
        // 1. Validate that only permissible self-service fields are being modified
        foreach (array_keys($changes) as $field) {
            if (!in_array($field, $this->allowedFields)) {
                throw ValidationException::withMessages([
                    'field' => "Field '{$field}' cannot be updated via personal change request. Authoritative fields require formal HR Personnel Actions.",
                ]);
            }
        }

        return DB::transaction(function () use ($employee, $changes, $comments) {
            $year = date('Y');
            $count = EmployeeProfileChangeRequest::where('tenant_id', $employee->tenant_id)->whereYear('created_at', $year)->count() + 1;
            $requestNumber = sprintf('PCR-%s-%06d', $year, $count);

            $request = EmployeeProfileChangeRequest::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'request_number' => $requestNumber,
                'status' => 'pending',
                'requested_at' => now(),
                'comments' => $comments,
            ]);

            foreach ($changes as $field => $newValue) {
                EmployeeProfileChangeRequestItem::create([
                    'tenant_id' => $employee->tenant_id,
                    'change_request_id' => $request->id,
                    'field_name' => $field,
                    'old_value' => $employee->{$field},
                    'new_value' => (string) $newValue,
                    'status' => 'pending',
                ]);
            }

            EmployeeProfileAudit::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'actor_id' => $employee->user_id,
                'event_name' => 'profile_change_requested',
                'section' => 'personal',
                'details' => ['request_number' => $requestNumber, 'fields' => array_keys($changes)],
            ]);

            return $request->load('items');
        });
    }

    public function approveRequest(EmployeeProfileChangeRequest $request, User $reviewer, ?string $comments = null): EmployeeProfileChangeRequest
    {
        return DB::transaction(function () use ($request, $reviewer, $comments) {
            $employee = $request->employee;
            $updatedFields = [];

            foreach ($request->items as $item) {
                $employee->{$item->field_name} = $item->new_value;
                $updatedFields[$item->field_name] = $item->new_value;
                $item->update(['status' => 'approved']);
            }

            // Update authoritative Core HR employee model
            $employee->save();

            $request->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'comments' => $comments ?? $request->comments,
            ]);

            EmployeeProfileAudit::create([
                'tenant_id' => $request->tenant_id,
                'employee_id' => $employee->id,
                'actor_id' => $reviewer->id,
                'event_name' => 'profile_change_approved',
                'section' => 'personal',
                'details' => ['request_number' => $request->request_number, 'updated_fields' => $updatedFields],
            ]);

            return $request->fresh(['items', 'employee']);
        });
    }

    public function rejectRequest(EmployeeProfileChangeRequest $request, User $reviewer, string $reason): EmployeeProfileChangeRequest
    {
        if (empty(trim($reason))) {
            throw ValidationException::withMessages(['reason' => 'Mandatory rejection reason required.']);
        }

        return DB::transaction(function () use ($request, $reviewer, $reason) {
            $request->items()->update(['status' => 'rejected']);

            $request->update([
                'status' => 'rejected',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            EmployeeProfileAudit::create([
                'tenant_id' => $request->tenant_id,
                'employee_id' => $request->employee_id,
                'actor_id' => $reviewer->id,
                'event_name' => 'profile_change_rejected',
                'details' => ['request_number' => $request->request_number, 'reason' => $reason],
            ]);

            return $request->fresh(['items']);
        });
    }
}
