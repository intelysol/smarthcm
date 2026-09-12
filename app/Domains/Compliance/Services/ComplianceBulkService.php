<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceAudit;
use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Compliance\Models\HcmEmployeeComplianceRequirement;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComplianceBulkService
{
    public function __construct(
        protected WorkPermitService $permitService,
        protected VisaService $visaService,
        protected ProfessionalLicenseService $licenseService
    ) {}

    /**
     * Bulk assign requirement to multiple employees.
     */
    public function bulkAssign(string $tenantId, string $requirementId, array $employeeIds, User $actor): array
    {
        $requirement = HcmComplianceRequirement::findOrFail($requirementId);
        $assigned = 0;

        DB::transaction(function () use ($tenantId, $requirementId, $employeeIds, $actor, &$assigned) {
            foreach ($employeeIds as $empId) {
                $record = HcmEmployeeComplianceRequirement::firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'employee_id' => $empId,
                        'requirement_id' => $requirementId,
                    ],
                    [
                        'status' => 'required',
                        'effective_from' => Carbon::today()->toDateString(),
                    ]
                );

                if ($record->wasRecentlyCreated) {
                    $assigned++;
                }
            }

            HcmComplianceAudit::create([
                'tenant_id' => $tenantId,
                'action' => 'requirement.bulk_assigned',
                'entity_type' => HcmComplianceRequirement::class,
                'entity_id' => $requirementId,
                'actor_id' => $actor->id,
                'details' => ['assigned_count' => $assigned, 'total_candidates' => count($employeeIds)],
            ]);
        });

        return [
            'requirement_id' => $requirementId,
            'assigned_count' => $assigned,
            'total_submitted' => count($employeeIds),
        ];
    }

    /**
     * Bulk upload with dry run validation preview.
     */
    public function bulkUpload(string $tenantId, string $category, array $rows, User $actor, bool $dryRun = true): array
    {
        $validItems = [];
        $errorItems = [];

        foreach ($rows as $index => $row) {
            $identifier = $row['employee_code'] ?? ($row['employee_number'] ?? ($row['email'] ?? null));
            $resolvedEmployee = null;
            $errors = [];

            if (empty($identifier)) {
                $errors[] = 'Missing employee_code or email.';
            } else {
                $resolvedEmployee = Employee::where('tenant_id', $tenantId)
                    ->where(function ($q) use ($identifier) {
                        $q->where('employee_code', $identifier)
                          ->orWhere('employee_number', $identifier)
                          ->orWhere('official_email', $identifier);
                    })->first();

                if (!$resolvedEmployee) {
                    $errors[] = "Employee not found for identifier '{$identifier}'.";
                }
            }

            // Category specific field validation
            if ($category === 'work_permit') {
                if (empty($row['permit_number'])) $errors[] = 'permit_number is required.';
                if (empty($row['country'])) $errors[] = 'country is required.';
                if (empty($row['expiry_date'])) $errors[] = 'expiry_date is required.';
            } elseif ($category === 'visa') {
                if (empty($row['visa_number'])) $errors[] = 'visa_number is required.';
                if (empty($row['expiry_date'])) $errors[] = 'expiry_date is required.';
            } elseif ($category === 'license') {
                if (empty($row['license_name'])) $errors[] = 'license_name is required.';
                if (empty($row['license_number'])) $errors[] = 'license_number is required.';
            }

            if (!empty($errors)) {
                $errorItems[] = [
                    'row_index' => $index + 1,
                    'identifier' => $identifier,
                    'errors' => $errors,
                ];
            } else {
                $validItems[] = [
                    'row_index' => $index + 1,
                    'employee_id' => $resolvedEmployee->id,
                    'data' => $row,
                ];
            }
        }

        // Execute if not dry run and has valid items
        $processedCount = 0;
        if (!$dryRun && !empty($validItems)) {
            DB::transaction(function () use ($category, $validItems, $actor, &$processedCount) {
                foreach ($validItems as $item) {
                    $empId = $item['employee_id'];
                    $data = $item['data'];

                    if ($category === 'work_permit') {
                        $this->permitService->addPermit($empId, $data, $actor);
                    } elseif ($category === 'visa') {
                        $this->visaService->addVisa($empId, $data, $actor);
                    } elseif ($category === 'license') {
                        $this->licenseService->addLicense($empId, $data, $actor);
                    }
                    $processedCount++;
                }
            });
        }

        return [
            'category' => $category,
            'dry_run' => $dryRun,
            'total_rows' => count($rows),
            'valid_count' => count($validItems),
            'error_count' => count($errorItems),
            'processed_count' => $processedCount,
            'errors' => $errorItems,
        ];
    }
}
