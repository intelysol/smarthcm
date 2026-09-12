<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitProgram;
use App\Domains\Benefits\Services\BenefitProgramService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitProgramController extends Controller
{
    public function __construct(
        protected BenefitProgramService $programService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $programs = $this->programService->getProgramsWithPlans($tenantId);

        return response()->json([
            'success' => true,
            'data' => $programs,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:60',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:30',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
            'configuration' => 'nullable|array',
        ]);

        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $program = $this->programService->createProgram($tenantId, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Benefit program created successfully.',
            'data' => $program->load('versions'),
        ], 201);
    }

    public function show(BenefitProgram $program): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $program->load(['plans.coverages', 'versions']),
        ]);
    }

    public function assignPlan(BenefitProgram $program, BenefitPlan $plan, Request $request): JsonResponse
    {
        $updatedPlan = $this->programService->assignPlanToProgram($program, $plan, $request->user());

        return response()->json([
            'success' => true,
            'message' => "Plan '{$plan->name}' assigned to program '{$program->name}'.",
            'data' => $updatedPlan,
        ]);
    }
}
