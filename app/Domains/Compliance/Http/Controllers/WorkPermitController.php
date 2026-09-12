<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\WorkPermitService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkPermitController extends Controller
{
    public function __construct(
        protected WorkPermitService $permitService
    ) {}

    public function index(string $employeeId): JsonResponse
    {
        $permits = $this->permitService->getPermits($employeeId);

        return response()->json([
            'status' => 'success',
            'data' => $permits,
        ]);
    }

    public function store(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'compliance_requirement_id' => 'nullable|uuid',
            'permit_type' => 'nullable|string|max:80',
            'permit_number' => 'required|string|max:100',
            'issuing_authority' => 'nullable|string|max:150',
            'country' => 'required|string|max:50',
            'issue_date' => 'nullable|date',
            'effective_from' => 'nullable|date',
            'expiry_date' => 'required|date',
            'sponsor' => 'nullable|string|max:150',
            'job_restriction' => 'nullable|string|max:255',
            'location_restriction' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'document_id' => 'nullable|uuid',
            'is_current' => 'boolean',
        ]);

        $permit = $this->permitService->addPermit($employeeId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Work permit added successfully.',
            'data' => $permit,
        ], 201);
    }

    public function update(Request $request, string $permitId): JsonResponse
    {
        $validated = $request->validate([
            'permit_type' => 'sometimes|string|max:80',
            'permit_number' => 'sometimes|string|max:100',
            'expiry_date' => 'sometimes|date',
            'sponsor' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'is_current' => 'boolean',
        ]);

        $permit = $this->permitService->updatePermit($permitId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Work permit updated successfully.',
            'data' => $permit,
        ]);
    }

    public function destroy(string $permitId): JsonResponse
    {
        $this->permitService->deletePermit($permitId);

        return response()->json([
            'status' => 'success',
            'message' => 'Work permit removed.',
        ]);
    }
}
