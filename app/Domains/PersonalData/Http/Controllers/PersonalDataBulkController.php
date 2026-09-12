<?php

namespace App\Domains\PersonalData\Http\Controllers;

use App\Domains\PersonalData\Models\HcmEmployeeDataBulkBatch;
use App\Domains\PersonalData\Services\PersonalDataBulkService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalDataBulkController extends Controller
{
    public function __construct(
        protected PersonalDataBulkService $bulkService
    ) {}

    /**
     * List bulk batches for tenant.
     */
    public function batches(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id ?? $request->query('tenant_id');
        $batches = $this->bulkService->getBatches($tenantId);

        return response()->json([
            'status' => 'success',
            'data' => $batches,
        ]);
    }

    /**
     * Get details for a specific batch.
     */
    public function batch(string $batchId): JsonResponse
    {
        $batch = HcmEmployeeDataBulkBatch::with('items')->findOrFail($batchId);

        return response()->json([
            'status' => 'success',
            'data' => $batch,
        ]);
    }

    /**
     * Upload and validate a bulk batch (dry-run supported).
     */
    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:personal,address,emergency_contact,dependent,identifier',
            'rows' => 'required|array|min:1',
            'dry_run' => 'nullable|boolean',
        ]);

        $tenantId = $request->user()->tenant_id ?? $request->input('tenant_id');
        $dryRun = $validated['dry_run'] ?? true;

        $batch = $this->bulkService->uploadBatch(
            $tenantId,
            $validated['category'],
            $validated['rows'],
            $request->user(),
            $dryRun
        );

        return response()->json([
            'status' => 'success',
            'message' => $dryRun ? 'Batch validated successfully in dry-run mode.' : 'Batch processed successfully.',
            'data' => $batch,
        ], 201);
    }

    /**
     * Process/commit a validated batch.
     */
    public function process(string $batchId): JsonResponse
    {
        $batch = $this->bulkService->processBatch($batchId);

        return response()->json([
            'status' => 'success',
            'message' => 'Batch processed and applied successfully.',
            'data' => $batch,
        ]);
    }
}
