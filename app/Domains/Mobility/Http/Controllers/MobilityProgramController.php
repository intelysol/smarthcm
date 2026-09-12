<?php

namespace App\Domains\Mobility\Http\Controllers;

use App\Domains\Mobility\Models\MobilityProgram;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilityProgramController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $programs = MobilityProgram::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['policyVersions'])
            ->latest()
            ->paginate(25);

        return response()->json($programs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|uuid',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'mobility_type' => 'sometimes|string|max:60',
            'description' => 'nullable|string',
            'min_duration_months' => 'sometimes|integer|min:1',
            'max_duration_months' => 'nullable|integer',
            'requires_relocation' => 'sometimes|boolean',
            'requires_compliance_check' => 'sometimes|boolean',
        ]);

        $tenantId = $validated['tenant_id'] ?? $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');

        $program = MobilityProgram::create(array_merge($validated, ['tenant_id' => $tenantId]));

        return response()->json($program, 201);
    }

    public function show(MobilityProgram $program): JsonResponse
    {
        return response()->json($program->load(['policyVersions.rules']));
    }
}
