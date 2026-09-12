<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmployeeDataBulkBatch;
use App\Domains\PersonalData\Models\HcmEmployeeDataBulkItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PersonalDataBulkService
{
    public function __construct(
        protected PersonalDataService $personalDataService,
        protected AddressService $addressService,
        protected EmergencyContactService $emergencyContactService,
        protected DependentService $dependentService,
        protected EmployeeIdentifierService $identifierService
    ) {}

    /**
     * Upload and validate a bulk batch with dry run preview.
     */
    public function uploadBatch(
        string $tenantId,
        string $category,
        array $rows,
        User $user,
        bool $dryRun = true
    ): HcmEmployeeDataBulkBatch {
        return DB::transaction(function () use ($tenantId, $category, $rows, $user, $dryRun) {
            $batchNumber = 'BLK-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $batch = HcmEmployeeDataBulkBatch::create([
                'tenant_id' => $tenantId,
                'batch_number' => $batchNumber,
                'category' => $category,
                'uploaded_by' => $user->id,
                'total_items' => count($rows),
                'valid_items' => 0,
                'error_items' => 0,
                'processed_items' => 0,
                'status' => 'draft',
            ]);

            $validCount = 0;
            $errorCount = 0;

            foreach ($rows as $row) {
                $identifier = $row['employee_identifier'] ?? ($row['employee_code'] ?? ($row['email'] ?? null));
                $payload = $row['data'] ?? $row;

                $validationErrors = [];
                $resolvedEmployee = null;

                if (empty($identifier)) {
                    $validationErrors[] = 'Missing employee_identifier (employee_code or email required).';
                } else {
                    $resolvedEmployee = Employee::where('tenant_id', $tenantId)
                        ->where(function ($q) use ($identifier) {
                            $q->where('employee_code', $identifier)
                              ->orWhere('employee_number', $identifier)
                              ->orWhere('official_email', $identifier)
                              ->orWhere('personal_email', $identifier);
                        })->first();

                    if (!$resolvedEmployee) {
                        $validationErrors[] = "No active employee found for identifier '{$identifier}'.";
                    }
                }

                // Field specific validation
                if (empty($validationErrors)) {
                    $errors = $this->validateRowData($category, $payload);
                    if (!empty($errors)) {
                        $validationErrors = array_merge($validationErrors, $errors);
                    }
                }

                $status = empty($validationErrors) ? 'valid' : 'error';
                if ($status === 'valid') {
                    $validCount++;
                } else {
                    $errorCount++;
                }

                HcmEmployeeDataBulkItem::create([
                    'tenant_id' => $tenantId,
                    'batch_id' => $batch->id,
                    'employee_identifier' => (string) $identifier,
                    'resolved_employee_id' => $resolvedEmployee?->id,
                    'payload' => $payload,
                    'status' => $status,
                    'validation_errors' => empty($validationErrors) ? null : $validationErrors,
                ]);
            }

            $batch->update([
                'valid_items' => $validCount,
                'error_items' => $errorCount,
                'status' => 'validated',
            ]);

            if (!$dryRun && $errorCount === 0) {
                $this->processBatch($batch->id);
            }

            return $batch->fresh(['items']);
        });
    }

    /**
     * Validate payload fields according to category.
     */
    protected function validateRowData(string $category, array $data): array
    {
        $errors = [];

        if ($category === 'personal') {
            if (isset($data['personal_email']) && !filter_var($data['personal_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid personal_email format.';
            }
        } elseif ($category === 'address') {
            if (empty($data['address_line_1'])) {
                $errors[] = 'address_line_1 is required for address import.';
            }
            if (empty($data['city'])) {
                $errors[] = 'city is required for address import.';
            }
            if (empty($data['country']) && empty($data['country_code'])) {
                $errors[] = 'country or country_code is required for address import.';
            }
        } elseif ($category === 'emergency_contact') {
            if (empty($data['name']) && empty($data['contact_name'])) {
                $errors[] = 'name is required for emergency contact.';
            }
            if (empty($data['relationship'])) {
                $errors[] = 'relationship is required for emergency contact.';
            }
            if (empty($data['primary_phone']) && empty($data['mobile'])) {
                $errors[] = 'primary_phone is required for emergency contact.';
            }
        } elseif ($category === 'dependent') {
            if (empty($data['first_name']) && empty($data['name'])) {
                $errors[] = 'first_name or name is required for dependent.';
            }
            if (empty($data['relationship'])) {
                $errors[] = 'relationship is required for dependent.';
            }
        } elseif ($category === 'identifier') {
            if (empty($data['identifier_type'])) {
                $errors[] = 'identifier_type is required.';
            }
            if (empty($data['identifier_value'])) {
                $errors[] = 'identifier_value is required.';
            }
        }

        return $errors;
    }

    /**
     * Process valid items in a validated batch.
     */
    public function processBatch(string $batchId): HcmEmployeeDataBulkBatch
    {
        return DB::transaction(function () use ($batchId) {
            $batch = HcmEmployeeDataBulkBatch::with('items')->findOrFail($batchId);

            if ($batch->status !== 'validated') {
                throw new InvalidArgumentException("Batch cannot be processed because it is in '{$batch->status}' state.");
            }

            $batch->update(['status' => 'processing']);

            $processed = 0;
            foreach ($batch->items as $item) {
                if ($item->status !== 'valid' || !$item->resolved_employee_id) {
                    continue;
                }

                $this->applyItem($batch->category, $item->resolved_employee_id, $item->payload);
                $item->update(['status' => 'processed']);
                $processed++;
            }

            $batch->update([
                'processed_items' => $processed,
                'status' => 'completed',
            ]);

            return $batch->fresh();
        });
    }

    /**
     * Apply individual item payload.
     */
    protected function applyItem(string $category, string $employeeId, array $payload): void
    {
        if ($category === 'personal') {
            $this->personalDataService->update($employeeId, $payload);
        } elseif ($category === 'address') {
            $this->addressService->addAddress($employeeId, $payload);
        } elseif ($category === 'emergency_contact') {
            $this->emergencyContactService->addContact($employeeId, $payload);
        } elseif ($category === 'dependent') {
            $this->dependentService->addDependent($employeeId, $payload);
        } elseif ($category === 'identifier') {
            $this->identifierService->addIdentifier($employeeId, $payload);
        }
    }

    /**
     * List batches for a tenant.
     */
    public function getBatches(string $tenantId)
    {
        return HcmEmployeeDataBulkBatch::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get();
    }
}
