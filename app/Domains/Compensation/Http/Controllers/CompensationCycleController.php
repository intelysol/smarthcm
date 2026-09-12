<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Http\Controllers;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Services\CompensationCycleService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompensationCycleController extends Controller
{
    public function __construct(
        protected CompensationCycleService $cycleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cycles = CompensationCycle::where('tenant_id', $request->user()->tenant_id)
            ->with(['budgets'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $cycles]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'cycle_type' => 'nullable|string|max:30',
            'currency' => 'nullable|string|max:3',
            'starts_on' => 'required|date',
            'ends_on' => 'required|date|after:starts_on',
            'effective_on' => 'required|date',
            'guidelines' => 'nullable|array',
            'eligibility_rules' => 'nullable|array',
            'is_confidential' => 'nullable|boolean',
        ]);

        $cycle = $this->cycleService->create($request->user(), $validated);

        return response()->json(['data' => $cycle], 201);
    }

    public function show(CompensationCycle $cycle): JsonResponse
    {
        $cycle->load(['budgets', 'recommendations', 'meritMatrices', 'calibrationSessions']);

        return response()->json(['data' => $cycle]);
    }

    public function configure(Request $request, CompensationCycle $cycle): JsonResponse
    {
        $validated = $request->validate([
            'guidelines' => 'required|array',
            'eligibility_rules' => 'nullable|array',
        ]);

        $updated = $this->cycleService->configure(
            $request->user(),
            $cycle,
            $validated['guidelines'],
            $validated['eligibility_rules'] ?? null
        );

        return response()->json(['data' => $updated]);
    }

    public function transition(Request $request, CompensationCycle $cycle): JsonResponse
    {
        $validated = $request->validate([
            'target_status' => 'required|string',
        ]);

        $updated = $this->cycleService->transition($request->user(), $cycle, $validated['target_status']);

        return response()->json(['data' => $updated]);
    }
}
