<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ComplianceBulkService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceBulkController extends Controller
{
    public function __construct(
        protected ComplianceBulkService $bulkService
    ) {}

    public function bulkAssign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'compliance_requirement_id' => 'required|uuid',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'required|uuid',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $results = $this->bulkService->bulkAssign(
            (string) $request->user()->tenant_id,
            $validated['compliance_requirement_id'],
            $validated['employee_ids'],
            $validated['due_date'] ?? null,
            (int) $request->user()->id,
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Bulk requirement assignment executed.',
            'data' => $results,
        ]);
    }

    public function bulkPreview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1',
            'records.*.employee_code' => 'required|string',
            'records.*.requirement_code' => 'required|string',
            'records.*.document_number' => 'nullable|string',
            'records.*.expiration_date' => 'nullable|date',
        ]);

        $preview = $this->bulkService->bulkPreview(
            (string) $request->user()->tenant_id,
            $validated['records']
        );

        return response()->json([
            'success' => true,
            'message' => 'Bulk import validation preview generated.',
            'data' => $preview,
        ]);
    }
}
