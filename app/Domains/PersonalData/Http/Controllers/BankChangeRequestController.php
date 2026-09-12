<?php

namespace App\Domains\PersonalData\Http\Controllers;

use App\Domains\PersonalData\Services\BankChangeRequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankChangeRequestController extends Controller
{
    public function __construct(
        protected BankChangeRequestService $bankRequestService
    ) {}

    /**
     * List bank change requests for tenant / payroll review.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id ?? $request->query('tenant_id');
        $requests = $this->bankRequestService->getPendingRequestsForPayroll($tenantId);

        return response()->json([
            'status' => 'success',
            'data' => $requests,
        ]);
    }

    /**
     * List bank change requests for a specific employee.
     */
    public function employeeRequests(string $employeeId): JsonResponse
    {
        $requests = $this->bankRequestService->getRequestsForEmployee($employeeId);

        return response()->json([
            'status' => 'success',
            'data' => $requests,
        ]);
    }

    /**
     * Submit a bank change request.
     */
    public function store(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'request_type' => 'nullable|string|in:add_account,update_account,deactivate_account',
            'bank_name' => 'required|string|max:150',
            'branch_name' => 'nullable|string|max:150',
            'account_title' => 'required|string|max:150',
            'account_number' => 'required|string|max:100',
            'routing_code' => 'nullable|string|max:50',
            'swift_bic' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
            'supporting_document_id' => 'nullable|uuid',
        ]);

        $changeRequest = $this->bankRequestService->submitRequest($employeeId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Bank detail change request submitted for payroll review.',
            'data' => $changeRequest,
        ], 201);
    }

    /**
     * Review (approve or reject) a bank change request.
     */
    public function review(Request $request, string $requestId): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:approve,reject',
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        $changeRequest = $this->bankRequestService->reviewRequest(
            $requestId,
            $validated['action'],
            $validated['rejection_reason'] ?? null,
            $request->user()
        );

        return response()->json([
            'status' => 'success',
            'message' => "Bank change request has been {$validated['action']}d.",
            'data' => $changeRequest,
        ]);
    }
}
