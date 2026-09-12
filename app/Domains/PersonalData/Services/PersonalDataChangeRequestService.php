<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmployeeDataChangeRequest;
use App\Domains\PersonalData\Models\HcmEmployeeDataChangeRequestItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PersonalDataChangeRequestService
{
    public function __construct(
        protected PersonalDataService $personalDataService,
        protected AddressService $addressService,
        protected EmergencyContactService $emergencyContactService,
        protected DependentService $dependentService,
        protected EmployeeIdentifierService $identifierService
    ) {}

    /**
     * Submit a change request with side-by-side items.
     */
    public function submitChangeRequest(
        string $employeeId,
        string $category,
        array $changes,
        array $meta = [],
        ?User $actor = null
    ): HcmEmployeeDataChangeRequest {
        return DB::transaction(function () use ($employeeId, $category, $changes, $meta) {
            $employee = Employee::findOrFail($employeeId);
            $requestNumber = 'DCR-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $changeRequest = HcmEmployeeDataChangeRequest::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'request_number' => $requestNumber,
                'category' => $category,
                'status' => 'pending',
                'effective_date' => $meta['effective_date'] ?? Carbon::today()->toDateString(),
                'reason' => $meta['reason'] ?? null,
                'supporting_document_id' => $meta['supporting_document_id'] ?? null,
            ]);

            foreach ($changes as $item) {
                HcmEmployeeDataChangeRequestItem::create([
                    'tenant_id' => $employee->tenant_id,
                    'change_request_id' => $changeRequest->id,
                    'target_entity' => $item['target_entity'] ?? $category,
                    'target_id' => $item['target_id'] ?? null,
                    'field_name' => $item['field_name'],
                    'old_value' => isset($item['old_value']) ? (is_array($item['old_value']) ? json_encode($item['old_value']) : (string)$item['old_value']) : null,
                    'new_value' => isset($item['new_value']) ? (is_array($item['new_value']) ? json_encode($item['new_value']) : (string)$item['new_value']) : null,
                    'status' => 'pending',
                ]);
            }

            return $changeRequest->load('items');
        });
    }

    /**
     * Review a change request (approve or reject).
     */
    public function reviewChangeRequest(
        string $requestId,
        string $action,
        ?string $reason = null,
        ?User $reviewer = null
    ): HcmEmployeeDataChangeRequest {
        return DB::transaction(function () use ($requestId, $action, $reason, $reviewer) {
            $request = HcmEmployeeDataChangeRequest::with('items')->findOrFail($requestId);

            if (!in_array($request->status, ['pending', 'under_review', 'pending_approval'])) {
                throw new InvalidArgumentException("Request cannot be {$action}ed because it is in '{$request->status}' state.");
            }

            if ($action === 'approve') {
                $request->update([
                    'status' => 'approved',
                    'reviewed_by' => $reviewer?->id,
                    'reviewed_at' => Carbon::now(),
                ]);

                // Apply changes to respective models
                $this->applyChanges($request);

                $request->update(['status' => 'applied']);
            } elseif ($action === 'reject') {
                $request->update([
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                    'reviewed_by' => $reviewer?->id,
                    'reviewed_at' => Carbon::now(),
                ]);

                $request->items()->update(['status' => 'rejected']);
            } else {
                throw new InvalidArgumentException("Invalid review action: {$action}");
            }

            return $request->fresh(['items']);
        });
    }

    /**
     * Apply approved changes to target records.
     */
    public function applyChanges(HcmEmployeeDataChangeRequest $request): void
    {
        $category = $request->category;
        $items = $request->items;

        if ($category === 'personal') {
            $updates = [];
            foreach ($items as $item) {
                $updates[$item->field_name] = $item->new_value;
                $item->update(['status' => 'applied']);
            }
            $this->personalDataService->update($request->employee_id, $updates);
        } elseif ($category === 'address') {
            $data = [];
            $targetId = null;
            foreach ($items as $item) {
                $data[$item->field_name] = $item->new_value;
                if ($item->target_id) {
                    $targetId = $item->target_id;
                }
                $item->update(['status' => 'applied']);
            }
            if ($targetId) {
                $this->addressService->updateAddress($targetId, $data);
            } else {
                $this->addressService->addAddress($request->employee_id, $data);
            }
        } elseif ($category === 'emergency_contact') {
            $data = [];
            $targetId = null;
            foreach ($items as $item) {
                $data[$item->field_name] = $item->new_value;
                if ($item->target_id) {
                    $targetId = $item->target_id;
                }
                $item->update(['status' => 'applied']);
            }
            if ($targetId) {
                $this->emergencyContactService->updateContact($targetId, $data);
            } else {
                $this->emergencyContactService->addContact($request->employee_id, $data);
            }
        } elseif ($category === 'dependent') {
            $data = [];
            $targetId = null;
            foreach ($items as $item) {
                $data[$item->field_name] = $item->new_value;
                if ($item->target_id) {
                    $targetId = $item->target_id;
                }
                $item->update(['status' => 'applied']);
            }
            if ($targetId) {
                $this->dependentService->updateDependent($targetId, $data);
            } else {
                $this->dependentService->addDependent($request->employee_id, $data);
            }
        } elseif ($category === 'identifier') {
            $data = [];
            $targetId = null;
            foreach ($items as $item) {
                $data[$item->field_name] = $item->new_value;
                if ($item->target_id) {
                    $targetId = $item->target_id;
                }
                $item->update(['status' => 'applied']);
            }
            if ($targetId) {
                $this->identifierService->updateIdentifier($targetId, $data);
            } else {
                $this->identifierService->addIdentifier($request->employee_id, $data);
            }
        }
    }

    /**
     * Get change requests for a tenant.
     */
    public function getRequests(string $tenantId, ?string $status = null): Collection
    {
        $query = HcmEmployeeDataChangeRequest::with(['employee', 'items'])
            ->where('tenant_id', $tenantId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Get change requests for a specific employee.
     */
    public function getRequestsForEmployee(string $employeeId): Collection
    {
        return HcmEmployeeDataChangeRequest::with('items')
            ->where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->get();
    }
}
