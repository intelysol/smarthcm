<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ComplianceRequirementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceRequirementController extends Controller
{
    public function __construct(
        protected ComplianceRequirementService $requirementService
    ) {}

    public function indexTypes(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id ?? $request->query('tenant_id');
        $types = \App\Domains\Compliance\Models\HcmComplianceRequirementType::where('tenant_id', $tenantId)->get();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id ?? $request->query('tenant_id');
        $requirements = $this->requirementService->getRequirements($tenantId);

        return response()->json([
            'success' => true,
            'data' => $requirements,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'requirement_type_id' => 'required|uuid',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:50',
            'legal_entity_id' => 'nullable|uuid',
            'branch_id' => 'nullable|uuid',
            'location_id' => 'nullable|uuid',
            'department_id' => 'nullable|uuid',
            'job_id' => 'nullable|uuid',
            'position_id' => 'nullable|uuid',
            'nationality_criteria' => 'nullable|string|max:50',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
            'is_mandatory' => 'boolean',
            'renewal_required' => 'boolean',
            'expiry_required' => 'boolean',
            'grace_period_days' => 'nullable|integer',
            'verification_required' => 'boolean',
            'document_required' => 'boolean',
            'approval_required' => 'boolean',
            'responsible_role' => 'nullable|string|max:50',
        ]);

        $tenantId = $request->user()->tenant_id ?? $request->input('tenant_id');
        $requirement = $this->requirementService->createRequirement($tenantId, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Compliance requirement created successfully.',
            'data' => $requirement,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:150',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:50',
            'legal_entity_id' => 'nullable|uuid',
            'department_id' => 'nullable|uuid',
            'position_id' => 'nullable|uuid',
            'nationality_criteria' => 'nullable|string|max:50',
            'is_mandatory' => 'boolean',
            'renewal_required' => 'boolean',
            'grace_period_days' => 'nullable|integer',
        ]);

        $requirement = $this->requirementService->updateRequirement($id, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Compliance requirement updated successfully.',
            'data' => $requirement,
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->requirementService->deactivateRequirement($id, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Compliance requirement deactivated.',
        ]);
    }
}
