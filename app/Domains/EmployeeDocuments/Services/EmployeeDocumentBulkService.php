<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentBulkBatch;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentBulkItem;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeDocumentBulkService
{
    public function __construct(protected ?EmployeeDocumentService $docService = null)
    {
        $this->docService = $docService ?? new EmployeeDocumentService();
    }

    public function validateBatch(User $uploader, HcmDocumentType $docType, array $items): EmployeeDocumentBulkBatch
    {
        return DB::transaction(function () use ($uploader, $docType, $items) {
            $batchNumber = 'BULK-' . strtoupper(Str::random(10));

            $batch = EmployeeDocumentBulkBatch::create([
                'tenant_id' => $uploader->tenant_id,
                'batch_number' => $batchNumber,
                'document_type_id' => $docType->id,
                'uploaded_by' => $uploader->id,
                'total_items' => count($items),
                'status' => 'validated',
            ]);

            $validCount = 0;
            $warningCount = 0;
            $errorCount = 0;

            foreach ($items as $itemData) {
                $identifier = $itemData['employee_identifier'];
                $fileName = $itemData['file_name'];
                $errors = [];

                // Resolve employee
                $employee = Employee::where('tenant_id', $uploader->tenant_id)
                    ->where(function ($q) use ($identifier) {
                        $q->where('employee_code', $identifier)
                          ->orWhere('employee_number', $identifier)
                          ->orWhere('official_email', $identifier);
                    })
                    ->first();

                if (!$employee) {
                    $errors[] = "Employee with identifier '{$identifier}' could not be resolved.";
                }

                // Check file extension
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'docx'])) {
                    $errors[] = "File type '{$ext}' is not supported.";
                }

                $status = empty($errors) ? 'valid' : 'invalid';
                if ($status === 'valid') {
                    $validCount++;
                } else {
                    $errorCount++;
                }

                EmployeeDocumentBulkItem::create([
                    'tenant_id' => $uploader->tenant_id,
                    'batch_id' => $batch->id,
                    'employee_identifier' => $identifier,
                    'resolved_employee_id' => $employee?->id,
                    'file_name' => $fileName,
                    'status' => $status,
                    'validation_errors' => !empty($errors) ? $errors : null,
                ]);
            }

            $batch->update([
                'valid_items' => $validCount,
                'warning_items' => $warningCount,
                'error_items' => $errorCount,
            ]);

            return $batch->fresh(['items', 'documentType']);
        });
    }

    public function processBatch(EmployeeDocumentBulkBatch $batch): EmployeeDocumentBulkBatch
    {
        $batch->update(['status' => 'processing']);

        $processed = 0;
        foreach ($batch->items()->where('status', 'valid')->get() as $item) {
            $employee = $item->employee;
            if ($employee) {
                $doc = $this->docService->storeDocument(
                    $batch->uploader,
                    $employee,
                    $batch->documentType,
                    null,
                    [
                        'title' => pathinfo($item->file_name, PATHINFO_FILENAME),
                        'source' => 'bulk_upload',
                    ]
                );

                $item->update([
                    'status' => 'processed',
                    'employee_document_id' => $doc->id,
                ]);
                $processed++;
            }
        }

        $batch->update([
            'processed_items' => $processed,
            'status' => 'completed',
        ]);

        return $batch;
    }
}
