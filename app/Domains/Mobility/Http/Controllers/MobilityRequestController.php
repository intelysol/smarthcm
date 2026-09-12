<?php

namespace App\Domains\Mobility\Http\Controllers;

use App\Domains\Mobility\Models\MobilityRequest;
use App\Domains\Mobility\Services\MobilityRequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilityRequestController extends Controller
{
    public function __construct(
        protected MobilityRequestService $requestService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $requests = MobilityRequest::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['employee', 'program', 'homeCompany', 'hostCompany'])
            ->latest()
            ->paginate(25);

        return response()->json($requests);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|uuid',
            'employee_id' => 'required|uuid',
            'program_id' => 'nullable|uuid',
            'mobility_type' => 'sometimes|string|max:60',
            'home_company_id' => 'required|uuid',
            'home_country' => 'required|string|max:80',
            'host_company_id' => 'required|uuid',
            'host_country' => 'required|string|max:80',
            'proposed_start_date' => 'required|date',
            'proposed_end_date' => 'nullable|date',
            'duration_months' => 'sometimes|integer|min:1',
            'business_justification' => 'required|string',
            'assignment_reason' => 'nullable|string|max:100',
        ]);

        $tenantId = $validated['tenant_id'] ?? $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');

        $req = $this->requestService->createRequest(array_merge($validated, ['tenant_id' => $tenantId]), $request->user());

        return response()->json($req, 201);
    }

    public function show(MobilityRequest $mobilityRequest): JsonResponse
    {
        return response()->json($mobilityRequest->load(['employee', 'program', 'homeCompany', 'hostCompany', 'assignment']));
    }

    public function submit(MobilityRequest $mobilityRequest, Request $request): JsonResponse
    {
        $submitted = $this->requestService->submitRequest($mobilityRequest, $request->user());
        return response()->json($submitted);
    }

    public function approve(MobilityRequest $mobilityRequest, Request $request): JsonResponse
    {
        $approved = $this->requestService->approveRequest($mobilityRequest, $request->user());
        return response()->json($approved);
    }

    public function reject(MobilityRequest $mobilityRequest, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $rejected = $this->requestService->rejectRequest($mobilityRequest, $validated['rejection_reason'], $request->user());
        return response()->json($rejected);
    }
}
