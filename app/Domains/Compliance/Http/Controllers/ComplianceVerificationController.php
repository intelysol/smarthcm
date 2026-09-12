<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ComplianceVerificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceVerificationController extends Controller
{
    public function __construct(
        protected ComplianceVerificationService $verificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $filters = $request->only(['verifiable_type', 'verifiable_id', 'status', 'method']);
        $verifications = $this->verificationService->listVerifications($tenantId, $filters, (int) $request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $verifications,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'verifiable_type' => 'required|string',
            'verifiable_id' => 'required|uuid',
            'source' => 'nullable|string|in:hr,compliance_officer,issuing_authority,document_verification,external_service,manual',
            'status' => 'required|string|in:pending,verified,rejected',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $verification = $this->verificationService->recordVerification(
            $validated['employee_id'],
            $validated['verifiable_type'],
            $validated['verifiable_id'],
            $validated['source'] ?? 'manual',
            $validated['status'],
            $validated['reference'] ?? null,
            $validated['notes'] ?? null,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Verification status recorded successfully.',
            'data' => $verification,
        ], 201);
    }

    public function history(Request $request, string $type, string $id): JsonResponse
    {
        $history = $this->verificationService->getVerificationHistory(
            (string) $request->user()->tenant_id,
            $type,
            $id
        );

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
