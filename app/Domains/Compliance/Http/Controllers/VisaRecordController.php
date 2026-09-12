<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\VisaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisaRecordController extends Controller
{
    public function __construct(
        protected VisaService $visaService
    ) {}

    public function index(string $employeeId): JsonResponse
    {
        $visas = $this->visaService->getVisas($employeeId);

        return response()->json([
            'status' => 'success',
            'data' => $visas,
        ]);
    }

    public function store(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'compliance_requirement_id' => 'nullable|uuid',
            'visa_type' => 'nullable|string|max:80',
            'visa_number' => 'required|string|max:100',
            'issuing_country' => 'nullable|string|max:50',
            'issuing_authority' => 'nullable|string|max:150',
            'issue_date' => 'nullable|date',
            'effective_from' => 'nullable|date',
            'expiry_date' => 'required|date',
            'entry_date' => 'nullable|date',
            'exit_date' => 'nullable|date',
            'is_multiple_entry' => 'boolean',
            'sponsor' => 'nullable|string|max:150',
            'residency_status' => 'nullable|string|max:50',
            'residency_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'document_id' => 'nullable|uuid',
            'is_current' => 'boolean',
        ]);

        $visa = $this->visaService->addVisa($employeeId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Visa record created successfully.',
            'data' => $visa,
        ], 201);
    }

    public function update(Request $request, string $visaId): JsonResponse
    {
        $validated = $request->validate([
            'visa_type' => 'sometimes|string|max:80',
            'visa_number' => 'sometimes|string|max:100',
            'expiry_date' => 'sometimes|date',
            'sponsor' => 'nullable|string|max:150',
            'residency_status' => 'nullable|string|max:50',
            'residency_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_current' => 'boolean',
        ]);

        $visa = $this->visaService->updateVisa($visaId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Visa record updated successfully.',
            'data' => $visa,
        ]);
    }

    public function destroy(string $visaId): JsonResponse
    {
        $this->visaService->deleteVisa($visaId);

        return response()->json([
            'status' => 'success',
            'message' => 'Visa record removed.',
        ]);
    }
}
