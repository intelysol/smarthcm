<?php

namespace App\Domains\EmployeeDocuments\Http\Controllers;

use App\Domains\EmployeeDocuments\Models\EmployeeDocumentBulkBatch;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentBulkService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDocumentBulkController extends Controller
{
    public function __construct(protected EmployeeDocumentBulkService $bulkService)
    {
    }

    public function validateBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_type_id' => 'required|uuid',
            'items' => 'required|array|min:1',
            'items.*.employee_identifier' => 'required|string',
            'items.*.file_name' => 'required|string',
        ]);

        $docType = HcmDocumentType::findOrFail($validated['document_type_id']);
        $batch = $this->bulkService->validateBatch($request->user(), $docType, $validated['items']);

        return response()->json($batch, 201);
    }

    public function processBatch(Request $request, string $batchId): JsonResponse
    {
        $batch = EmployeeDocumentBulkBatch::with('items')->findOrFail($batchId);
        $processed = $this->bulkService->processBatch($batch);

        return response()->json($processed);
    }
}
