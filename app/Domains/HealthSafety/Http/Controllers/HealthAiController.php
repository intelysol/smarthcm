<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Services\HealthAiAdvisoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthAiController extends Controller
{
    public function __construct(
        protected HealthAiAdvisoryService $aiService
    ) {}

    public function analyzeIncident(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|min:10',
            'incident_type' => 'nullable|string',
        ]);

        $analysis = $this->aiService->analyzeIncident($validated['description'], $validated['incident_type'] ?? null);

        return response()->json([
            'status' => 'success',
            'data' => $analysis,
        ]);
    }

    public function adviseAccommodations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restriction_description' => 'required|string|min:5',
        ]);

        $advice = $this->aiService->adviseAccommodations($validated['restriction_description']);

        return response()->json([
            'status' => 'success',
            'data' => $advice,
        ]);
    }
}
