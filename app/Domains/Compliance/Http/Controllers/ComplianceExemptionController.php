<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ComplianceExemptionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceExemptionController extends Controller
{
    public function __construct(
        protected ComplianceExemptionService $exemptionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $filters = $request->only(['employee_id', 'status', 'compliance_requirement_id']);
        $exemptions = $this->exemptionService->listExemptions($tenantId, $filters, (int) $request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $exemptions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'requirement_id' => 'required|uuid',
            'reason' => 'required|string|max:255',
            'effective_from' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:effective_from',
            'supporting_document_id' => 'nullable|uuid',
        ]);

        $exemption = $this->exemptionService->requestExemption(
            $validated['employee_id'],
            $validated['requirement_id'],
            $validated['reason'],
            $validated['effective_from'],
            $validated['expiry_date'],
            $validated['supporting_document_id'] ?? null,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Compliance exemption requested successfully.',
            'data' => $exemption,
        ], 201);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $exemption = $this->exemptionService->approveExemption(
            $id,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Compliance exemption approved successfully.',
            'data' => $exemption,
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $exemption = $this->exemptionService->rejectExemption(
            (string) $request->user()->tenant_id,
            $id,
            (int) $request->user()->id,
            $validated['rejection_reason']
        );

        return response()->json([
            'success' => true,
            'message' => 'Compliance exemption rejected.',
            'data' => $exemption,
        ]);
    }

    public function revoke(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $exemption = $this->exemptionService->revokeExemption(
            (string) $request->user()->tenant_id,
            $id,
            (int) $request->user()->id,
            $validated['reason']
        );

        return response()->json([
            'success' => true,
            'message' => 'Compliance exemption revoked.',
            'data' => $exemption,
        ]);
    }
}
