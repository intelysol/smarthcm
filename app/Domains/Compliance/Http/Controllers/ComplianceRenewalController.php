<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ComplianceRenewalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceRenewalController extends Controller
{
    public function __construct(
        protected ComplianceRenewalService $renewalService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $filters = $request->only(['status', 'renewable_type', 'search']);
        $renewals = $this->renewalService->listRenewals($tenantId, $filters, (int) $request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $renewals,
        ]);
    }

    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'renewable_type' => 'required|string',
            'renewable_id' => 'required|uuid',
            'submission_deadline' => 'nullable|date',
            'cost_center' => 'nullable|string|max:100',
            'fee_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
        ]);

        $renewal = $this->renewalService->initiateRenewal(
            (string) $request->user()->tenant_id,
            $validated,
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Compliance renewal process initiated successfully.',
            'data' => $renewal,
        ], 201);
    }

    public function updateProgress(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:draft,in_progress,submitted,approved,rejected,cancelled',
            'authority_tracking_number' => 'nullable|string|max:100',
            'submission_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $renewal = $this->renewalService->updateProgress(
            (string) $request->user()->tenant_id,
            $id,
            $validated,
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Renewal progress updated successfully.',
            'data' => $renewal,
        ]);
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'new_expiration_date' => 'required|date|after:today',
            'new_document_id' => 'nullable|uuid',
            'notes' => 'nullable|string',
        ]);

        $renewal = $this->renewalService->completeRenewal(
            (string) $request->user()->tenant_id,
            $id,
            $validated['new_expiration_date'],
            $validated['new_document_id'] ?? null,
            (int) $request->user()->id,
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Compliance renewal completed and record updated.',
            'data' => $renewal,
        ]);
    }
}
