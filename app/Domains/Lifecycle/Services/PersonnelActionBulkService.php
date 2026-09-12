<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Enums\BulkBatchStatus;
use App\Domains\Lifecycle\Models\PersonnelActionBulkBatch;
use App\Domains\Lifecycle\Models\PersonnelActionBulkItem;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PersonnelActionBulkService
{
    public function __construct(protected ?PersonnelActionService $actionService = null)
    {
        $this->actionService = $actionService ?? new PersonnelActionService();
    }

    public function createBatch(User $user, PersonnelActionType $actionType, string $name, array $itemsData, ?string $effectiveDate = null): PersonnelActionBulkBatch
    {
        return DB::transaction(function () use ($user, $actionType, $name, $itemsData, $effectiveDate) {
            $batch = PersonnelActionBulkBatch::create([
                'tenant_id' => $user->tenant_id,
                'action_type_id' => $actionType->id,
                'created_by' => $user->id,
                'name' => $name,
                'status' => BulkBatchStatus::DRAFT->value,
                'total_items' => count($itemsData),
                'effective_date' => $effectiveDate ?? now()->toDateString(),
            ]);

            foreach ($itemsData as $data) {
                PersonnelActionBulkItem::create([
                    'tenant_id' => $user->tenant_id,
                    'batch_id' => $batch->id,
                    'employee_id' => $data['employee_id'],
                    'payload' => $data['payload'] ?? [],
                    'validation_status' => 'pending',
                ]);
            }

            return $batch;
        });
    }

    public function validateDryRun(PersonnelActionBulkBatch $batch): PersonnelActionBulkBatch
    {
        $valid = 0;
        $warning = 0;
        $error = 0;

        foreach ($batch->items()->get() as $item) {
            $emp = Employee::find($item->employee_id);
            if (!$emp) {
                $item->update([
                    'validation_status' => 'error',
                    'validation_errors' => ['employee' => 'Employee does not exist or has been deleted.'],
                ]);
                $error++;
                continue;
            }

            if ($emp->employment_status === 'terminated') {
                $item->update([
                    'validation_status' => 'error',
                    'validation_errors' => ['status' => 'Cannot apply personnel action to terminated employee.'],
                ]);
                $error++;
                continue;
            }

            $item->update([
                'validation_status' => 'valid',
                'validation_errors' => null,
            ]);
            $valid++;
        }

        $batch->update([
            'status' => BulkBatchStatus::VALIDATED->value,
            'valid_items' => $valid,
            'warning_items' => $warning,
            'error_items' => $error,
        ]);

        return $batch;
    }

    public function executeBatch(PersonnelActionBulkBatch $batch, User $user): PersonnelActionBulkBatch
    {
        $batch->update([
            'status' => BulkBatchStatus::PROCESSING->value,
            'started_at' => now(),
        ]);

        $successful = 0;
        $failed = 0;

        foreach ($batch->items()->where('validation_status', 'valid')->get() as $item) {
            try {
                $request = $this->actionService->createRequest($user, [
                    'employee_id' => $item->employee_id,
                    'action_type_id' => $batch->action_type_id,
                    'effective_date' => $batch->effective_date ?? now()->toDateString(),
                    'reason' => "Batch action: {$batch->name}",
                    'source' => 'bulk',
                    'changes' => $item->payload['changes'] ?? [],
                ]);

                // Submit and approve & execute if effective today
                $this->actionService->submitRequest($request, $user);
                $this->actionService->approveRequest($request, $user, 'Bulk batch auto-approval');

                $item->update([
                    'execution_status' => 'successful',
                    'personnel_action_id' => $request->id,
                ]);
                $successful++;
            } catch (\Exception $e) {
                $item->update([
                    'execution_status' => 'failed',
                    'execution_error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $batch->update([
            'status' => BulkBatchStatus::COMPLETED->value,
            'processed_items' => $successful + $failed,
            'successful_items' => $successful,
            'failed_items' => $failed,
            'completed_at' => now(),
        ]);

        return $batch;
    }
}
