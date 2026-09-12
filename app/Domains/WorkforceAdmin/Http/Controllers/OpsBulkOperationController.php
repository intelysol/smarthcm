<?php

namespace App\Domains\WorkforceAdmin\Http\Controllers;

use App\Domains\WorkforceAdmin\Models\OpsBulkOperation;
use App\Domains\WorkforceAdmin\Services\BulkOperationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpsBulkOperationController extends Controller
{
    public function __construct(
        protected BulkOperationService $bulkService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $operations = OpsBulkOperation::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['creator', 'validation'])
            ->latest()
            ->paginate(25);

        return response()->json($operations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operation_type' => 'required|string|max:80',
            'reason' => 'required|string|max:500',
            'effective_date' => 'sometimes|date',
            'proposed_changes' => 'required|array',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'uuid',
        ]);

        $bulkOp = $this->bulkService->createBulkOperation($validated, $request->user());

        return response()->json($bulkOp->load('items'), 201);
    }

    public function show(OpsBulkOperation $bulkOperation): JsonResponse
    {
        return response()->json($bulkOperation->load(['creator', 'approver', 'validation', 'items.employee', 'errors']));
    }

    public function validateAndDryRun(OpsBulkOperation $bulkOperation): JsonResponse
    {
        $validation = $this->bulkService->validateAndDryRun($bulkOperation);
        return response()->json($validation);
    }

    public function approve(OpsBulkOperation $bulkOperation, Request $request): JsonResponse
    {
        $approved = $this->bulkService->approveBulkOperation($bulkOperation, $request->user());
        return response()->json($approved);
    }

    public function execute(OpsBulkOperation $bulkOperation, Request $request): JsonResponse
    {
        $executed = $this->bulkService->executeBulkOperation($bulkOperation, $request->user());
        return response()->json($executed);
    }
}
