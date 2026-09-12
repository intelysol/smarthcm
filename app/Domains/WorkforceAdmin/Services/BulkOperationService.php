<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use App\Domains\WorkforceAdmin\Enums\BulkOperationStatus;
use App\Domains\WorkforceAdmin\Models\OpsBulkOperation;
use App\Domains\WorkforceAdmin\Models\OpsBulkOperationError;
use App\Domains\WorkforceAdmin\Models\OpsBulkOperationItem;
use App\Domains\WorkforceAdmin\Models\OpsBulkOperationValidation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BulkOperationService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Create a draft bulk operation with item references.
     */
    public function createBulkOperation(array $data, User $actor): OpsBulkOperation
    {
        $operationNumber = 'BLK-' . strtoupper(Str::random(8));

        $employeeIds = $data['employee_ids'] ?? [];

        $bulkOp = OpsBulkOperation::create([
            'tenant_id' => $actor->tenant_id,
            'operation_number' => $operationNumber,
            'operation_type' => $data['operation_type'],
            'status' => BulkOperationStatus::DRAFT->value,
            'reason' => $data['reason'],
            'effective_date' => $data['effective_date'] ?? now()->toDateString(),
            'proposed_changes' => $data['proposed_changes'],
            'total_records' => count($employeeIds),
            'created_by' => $actor->id,
        ]);

        // Create operation items
        foreach ($employeeIds as $empId) {
            $employee = Employee::find($empId);
            OpsBulkOperationItem::create([
                'tenant_id' => $bulkOp->tenant_id,
                'bulk_operation_id' => $bulkOp->id,
                'employee_id' => $empId,
                'current_values' => $employee ? [
                    'department_id' => $employee->department_id,
                    'designation_id' => $employee->designation_id,
                    'branch_id' => $employee->branch_id,
                ] : null,
                'target_values' => $data['proposed_changes'],
                'status' => 'pending',
            ]);
        }

        return $bulkOp;
    }

    /**
     * Perform dry-run validation without altering underlying domain records.
     */
    public function validateAndDryRun(OpsBulkOperation $bulkOp): OpsBulkOperationValidation
    {
        $bulkOp->update(['status' => BulkOperationStatus::VALIDATING->value]);

        $validCount = 0;
        $errorCount = 0;
        $warningCount = 0;

        // Clear previous validations/errors
        OpsBulkOperationError::where('bulk_operation_id', $bulkOp->id)->delete();
        OpsBulkOperationValidation::where('bulk_operation_id', $bulkOp->id)->delete();

        foreach ($bulkOp->items as $item) {
            $employee = $item->employee;

            if (!$employee) {
                $errorCount++;
                $item->update(['status' => 'error', 'execution_error' => 'Employee does not exist']);
                OpsBulkOperationError::create([
                    'tenant_id' => $bulkOp->tenant_id,
                    'bulk_operation_id' => $bulkOp->id,
                    'bulk_operation_item_id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'error_code' => 'EMP_NOT_FOUND',
                    'error_message' => 'Employee record not found in Core HR.',
                ]);
                continue;
            }

            if ($employee->employment_status !== 'active') {
                $warningCount++;
                $item->update(['status' => 'valid']); // non-blocking warning
            } else {
                $item->update(['status' => 'valid']);
            }

            $validCount++;
        }

        $impactedDomains = ['core_hr'];
        if (isset($bulkOp->proposed_changes['department_id'])) {
            $impactedDomains[] = 'payroll'; // cost center alignment
            $impactedDomains[] = 'benefits'; // eligibility review
        }

        $validation = OpsBulkOperationValidation::create([
            'tenant_id' => $bulkOp->tenant_id,
            'bulk_operation_id' => $bulkOp->id,
            'valid_count' => $validCount,
            'warning_count' => $warningCount,
            'error_count' => $errorCount,
            'impacted_domains' => array_values(array_unique($impactedDomains)),
            'validation_summary' => [
                'ready_to_execute' => $errorCount === 0,
                'note' => $errorCount === 0 ? 'Validation passed successfully.' : "{$errorCount} blocking errors found.",
            ],
        ]);

        $bulkOp->update(['status' => BulkOperationStatus::DRY_RUN_READY->value]);

        return $validation;
    }

    /**
     * Approve bulk operation.
     */
    public function approveBulkOperation(OpsBulkOperation $bulkOp, User $approver): OpsBulkOperation
    {
        $bulkOp->update([
            'status' => BulkOperationStatus::APPROVED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $bulkOp;
    }

    /**
     * Execute bulk operation idempotently.
     */
    public function executeBulkOperation(OpsBulkOperation $bulkOp, ?User $actor = null): OpsBulkOperation
    {
        $bulkOp->update(['status' => BulkOperationStatus::EXECUTING->value]);

        $successful = 0;
        $failed = 0;

        foreach ($bulkOp->items as $item) {
            if ($item->status === 'error') {
                $failed++;
                continue;
            }

            $employee = $item->employee;
            if ($employee) {
                // Apply changes to Core HR employee
                $employee->update($bulkOp->proposed_changes);
                $item->update(['status' => 'executed']);
                $successful++;
            } else {
                $item->update(['status' => 'failed', 'execution_error' => 'Employee missing']);
                $failed++;
            }
        }

        $status = ($failed === 0) ? BulkOperationStatus::COMPLETED->value : BulkOperationStatus::FAILED->value;

        $bulkOp->update([
            'status' => $status,
            'processed_records' => $successful + $failed,
            'successful_records' => $successful,
            'failed_records' => $failed,
            'executed_at' => now(),
        ]);

        $this->auditService->record(
            tenantId: $bulkOp->tenant_id,
            eventType: 'bulk_operation_executed',
            action: 'execute',
            entityType: 'OpsBulkOperation',
            entityId: $bulkOp->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: null,
            after: $bulkOp->toArray()
        );

        return $bulkOp;
    }
}
