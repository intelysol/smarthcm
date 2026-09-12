<?php

namespace App\Domains\PersonalData\Http\Controllers;

use App\Domains\PersonalData\Services\PersonalDataChangeRequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalDataChangeRequestController extends Controller
{
    public function __construct(
        protected PersonalDataChangeRequestService $changeRequestService
    ) {}

    /**
     * List change requests for tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id ?? $request->query('tenant_id');
        $status = $request->query('status');

        $requests = $this->changeRequestService->getRequests($tenantId, $status);

        return response()->json([
            'status' => 'success',
            'data' => $requests,
        ]);
    }

    /**
     * List change requests for employee.
     */
    public function employeeRequests(string $employeeId): JsonResponse
    {
        $requests = $this->changeRequestService->getRequestsForEmployee($employeeId);

        return response()->json([
            'status' => 'success',
            'data' => $requests,
        ]);
    }

    /**
     * Submit a data change request.
     */
    public function store(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:personal,address,emergency_contact,dependent,identifier',
            'effective_date' => 'nullable|date',
            'reason' => 'nullable|string|max:255',
            'supporting_document_id' => 'nullable|uuid',
            'changes' => 'required|array|min:1',
            'changes.*.target_entity' => 'nullable|string',
            'changes.*.target_id' => 'nullable|uuid',
            'changes.*.field_name' => 'required|string',
            'changes.*.old_value' => 'nullable',
            'changes.*.new_value' => 'nullable',
        ]);

        $meta = [
            'effective_date' => $validated['effective_date'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'supporting_document_id' => $validated['supporting_document_id'] ?? null,
        ];

        $changeRequest = $this->changeRequestService->submitChangeRequest(
            $employeeId,
            $validated['category'],
            $validated['changes'],
            $meta,
            $request->user()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data change request submitted successfully.',
            'data' => $changeRequest,
        ], 201);
    }

    /**
     * Review (approve or reject) a data change request.
     */
    public function review(Request $request, string $requestId): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:approve,reject',
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        $changeRequest = $this->changeRequestService->reviewChangeRequest(
            $requestId,
            $validated['action'],
            $validated['rejection_reason'] ?? null,
            $request->user()
        );

        return response()->json([
            'status' => 'success',
            'message' => "Change request {$validated['action']}d successfully.",
            'data' => $changeRequest,
        ]);
    }
}
